<?php
declare(strict_types=1);
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
namespace App\Http\Tag\Admin;

final class AdminController
{
    public function __construct(private string $baseUrl = 'http://localhost:8080') {}

    public function index(array $req): array
    {
        $q = isset($_GET['q']) ? (string)$_GET['q'] : '';
        $html = $this->render('index.php', ['q'=>$q]);
        return [200, ['Content-Type'=>'text/html; charset=utf-8'], $html];
    }

    public function show(array $req, string $id): array
    {
        $html = $this->render('show.php', ['id'=>$id]);
        return [200, ['Content-Type'=>'text/html; charset=utf-8'], $html];
    }

    public function assign(array $req, string $id): array
    {
        $html = $this->render('assign.php', ['id'=>$id]);
        return [200, ['Content-Type'=>'text/html; charset=utf-8'], $html];
    }

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
