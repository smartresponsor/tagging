<?php

declare(strict_types=1);

namespace App\Tagging\Service\Http\Tag;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCaseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TagCreateService extends AbstractTagService
{
    public function __construct(private TagCreateUseCaseInterface $useCase) {}

    public function __invoke(Request $request): Response
    {
        try {
            return $this->result($this->useCase->execute(new TagCreateCommand(
                tenant: $this->tenant($request),
                payload: $this->payload($request),
            )));
        } catch (\Throwable $error) {
            return $this->failure($error);
        }
    }
}
