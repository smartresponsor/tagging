<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Http\Tag\Admin;

/**
 *
 */

/**
 *
 */
final readonly class AdminController
{
    /**
     * @param string $baseUrl
     */
    public function __construct(private string $baseUrl = 'http://localhost:8080')
    {
    }

    /**
     * @param array $_req
     * @return array
     */
    public function index(array $_req): array
    {
        unset($req);

        $q = isset($_GET['q']) ? (string)$_GET['q'] : '';
        $html = $this->render('index.php', ['q' => $q]);
        return [200, ['Content-Type' => 'text/html; charset=utf-8'], $html];
    }

    /**
     * @param array $_req
     * @param string $id
     * @return array
     */
    public function show(array $_req, string $id): array
    {
        unset($req);

        $html = $this->render('show.php', ['id' => $id]);
        return [200, ['Content-Type' => 'text/html; charset=utf-8'], $html];
    }

    /**
     * @param array $_req
     * @param string $id
     * @return array
     */
    public function assign(array $_req, string $id): array
    {
        unset($req);

        $html = $this->render('assign.php', ['id' => $id]);
        return [200, ['Content-Type' => 'text/html; charset=utf-8'], $html];
    }

    /**
     * @param string $view
     * @param array $data
     * @return string
     */
    private function render(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include __DIR__ . '/View/' . $view;
        $content = ob_get_clean();
        ob_start();
        include __DIR__ . '/View/layout.php';
        return (string)ob_get_clean();
    }
}
