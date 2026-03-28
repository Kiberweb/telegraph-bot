<?php

namespace App\Service;

use App\Entity\Article;
use Symfony\Component\Panther\Client;

class TelegraphService
{
    private Client $client;

    /**
     * @param string|null $externalDriverUrl Наприклад 'http://localhost:9515'
     */
    public function __construct(?string $externalDriverUrl = null) {
        if ($externalDriverUrl) {
            $this->client = Client::createSeleniumClient($externalDriverUrl);
        } else {
            $this->client = Client::createChromeClient();
        }
    }

    public function publish(Article $article): string {
        $this->client->request('GET', 'https://telegra.ph');
        $this->client->waitFor('#_tl_editor');

        $this->client->executeScript("
            (function(title, author, content) {
                if (typeof quill === 'undefined') return;

                quill.setText('');
                quill.insertText(0, title + '\\n', 'user');
                quill.formatLine(0, title.length, 'blockTitle', true);

                var authorPos = title.length + 1;
                quill.insertText(authorPos, author + '\\n', 'user');
                quill.formatLine(authorPos, author.length, 'blockAuthor', true);

                var contentPos = authorPos + author.length + 1;
                quill.clipboard.dangerouslyPasteHTML(contentPos, content, 'user');
                
                quill.update('user');
                if (typeof draftSave === 'function') draftSave();
            })(arguments[0], arguments[1], arguments[2]);
        ", [
            $article->title,
            $article->author,
            '<p>' . str_replace(PHP_EOL, '</p><p>', $article->content) . '</p>'
        ]);

        $article->createdAt = date('Y-m-d H:i:s');
        
        // Даємо час на внутрішню валідацію Telegra.ph
        usleep(500000);

        // Натискаємо кнопку Publish
        $this->client->executeScript("
            var btn = document.getElementById('_publish_button');
            if (btn) btn.click();
        ");

        try {
            // 1. Чекаємо, поки з'явиться кнопка Edit
            $this->client->waitFor('#_edit_button', 15);
            
            // 2. ДОДАТКОВО чекаємо, поки URL зміниться з головної на сторінку статті
            $this->client->wait(10)->until(function ($driver) {
                $url = $driver->getCurrentURL();
                return (
                    $url !== 'https://telegra.ph/' && 
                    $url !== 'https://telegra.ph' && 
                    str_contains($url, 'telegra.ph/')
                );
            });

            return $this->client->getCurrentURL();
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
