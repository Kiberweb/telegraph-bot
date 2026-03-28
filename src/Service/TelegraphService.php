<?php

namespace App\Service;

use App\Entity\Article;
use Symfony\Component\Panther\Client;

class TelegraphService
{
    private Client $client;

    public function __construct() {
        $this->client = Client::createChromeClient();
    }

    public function publish(Article $article): string {
        $this->client->request('GET', 'https://telegra.ph');
        $this->client->waitFor('#_tl_editor');

        $this->client->executeScript("
            (function(title, author, content) {
                if (typeof quill === 'undefined') {
                    throw new Error('Quill is not initialized');
                }

                // 1. Повністю очищуємо редактор
                quill.deleteText(0, quill.getLength(), 'silent');

                // 2. Вставляємо заголовок і форматуємо його як blockTitle
                // Додаємо символ переведення рядка, щоб розділити блоки
                quill.insertText(0, title + '\\n', 'silent');
                quill.formatLine(0, 1, 'blockTitle', true, 'silent');

                // 3. Вставляємо автора і форматуємо як blockAuthor
                var authorPos = title.length + 1;
                quill.insertText(authorPos, author + '\\n', 'silent');
                quill.formatLine(authorPos, 1, 'blockAuthor', true, 'silent');

                // 4. Вставляємо основний контент
                var contentPos = authorPos + author.length + 1;
                quill.clipboard.dangerouslyPasteHTML(contentPos, content, 'silent');
                
                // 5. Оновлюємо внутрішній стан та викликаємо системну валідацію
                quill.update('user');
                
                // Додатково перевіряємо чи jQuery бачить текст (для функції savePage)
                var titleEl = document.querySelector('h1');
                if (titleEl && titleEl.innerText.length < 2) {
                    titleEl.innerText = title;
                }

                // 6. Викликаємо нативне збереження
                if (typeof savePage === 'function') {
                    savePage();
                } else {
                    document.getElementById('_publish_button').click();
                }
            })(arguments[0], arguments[1], arguments[2]);
        ", [
            $article->title,
            $article->author,
            '<p>' . str_replace(PHP_EOL, '</p><p>', $article->content) . '</p>'
        ]);

        $article->createdAt = date('Y-m-d H:i:s');

        try {
            // Очікуємо перенаправлення або появу кнопки Edit
            $this->client->waitFor('#_edit_button', 20);
            
            $url = $this->client->getCurrentURL();
            if ($url === 'https://telegra.ph/') {
                throw new \Exception("Публікація начебто пройшла, але URL залишився головним.");
            }
            
            return $url;
        } catch (\Throwable $e) {
            $errorMsg = $this->client->executeScript("
                var err = document.getElementById('_error_msg');
                return err ? err.innerText : 'Unknown Error/Timeout';
            ");
            throw new \Exception("Telegra.ph Error: " . $errorMsg . " (URL: " . $this->client->getCurrentURL() . ")");
        }
    }

    public function __destruct() {
        if (isset($this->client)) {
            $this->client->quit();
        }
    }
}
