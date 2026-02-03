<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);
namespace App\Service\Tag;

use App\ServiceInterface\Tag\TagRepositoryInterface as TagRepositoryContract;

final class TagPolicyService
{
    public function __construct(
        private TagValidator $validator,
        private array $cfg // from config/tag_policy.yaml
    ){}

    public function normalizeSlug(string $input): string
    {
        $s = $this->validator->normalizeSlug($input);
        if (!empty($this->cfg['normalize']['lowercase'])) $s = strtolower($s);
        return $s;
    }

    public function validateBeforeCreate(TagRepositoryContract $repo, string $label, ?string $slug=null): void
    {
        $this->validator->validateLabel($label);
        $slug = $slug ?? $this->normalizeSlug($label);
        $this->applyRules($slug);
        $this->validator->validateSlug($slug);
        $this->validator->ensureUniqueness($repo, $slug, null);
    }

    public function validateBeforeUpdate(TagRepositoryContract $repo, string $tagId, string $label, ?string $slug=null): void
    {
        $this->validator->validateLabel($label);
        if ($slug !== null) {
            $slug = $this->normalizeSlug($slug);
            $this->applyRules($slug);
            $this->validator->validateSlug($slug);
            $this->validator->ensureUniqueness($repo, $slug, $tagId);
        }
    }

    private function applyRules(string $slug): void
    {
        if (in_array($slug, $this->cfg['reserved_slugs'] ?? [], true)) {
            throw new \InvalidArgumentException('slug_reserved');
        }
        foreach (($this->cfg['denied_prefixes'] ?? []) as $p) {
            if ($p !== '' && str_starts_with($slug, $p)) {
                throw new \InvalidArgumentException('slug_denied_prefix');
            }
        }
        foreach (($this->cfg['denied_regex'] ?? []) as $rx) {
            if ($rx !== '' && @preg_match('/'.$rx.'/', $slug)) {
                if (preg_match('/'.$rx.'/', $slug)) {
                    throw new \InvalidArgumentException('slug_denied_regex');
                }
            }
        }
    }
}
