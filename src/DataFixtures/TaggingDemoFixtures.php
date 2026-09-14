<?php

declare(strict_types=1);

namespace App\Tagging\DataFixtures;

use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagPolicyEntity;
use App\Tagging\Entity\Tag\TagProposalEntity;
use App\Tagging\Entity\Tag\TagRedirectEntity;
use App\Tagging\Entity\Tag\TagRelationEntity;
use App\Tagging\Entity\Tag\TagSchemeEntity;
use App\Tagging\Entity\Tag\TagSynonymEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TaggingDemoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (['tenant-a', 'tenant-b'] as $tenantIndex => $tenant) {
            $first = TagEntity::create($tenant, sprintf('tag-%s-000000000000000001', $tenantIndex), sprintf('%s-primary', $tenant), 'Primary tag');
            $second = TagEntity::create($tenant, sprintf('tag-%s-000000000000000002', $tenantIndex), sprintf('%s-secondary', $tenant), 'Secondary tag');
            $second->setFlags(true, false);

            $manager->persist(new TagPolicyEntity($tenant, [
                'visibility' => 0 === $tenantIndex ? 'public' : 'restricted',
                'approval' => ['required' => true, 'moderators' => ['team-tagging']],
            ]));
            $manager->persist(TagSchemeEntity::create($tenant, sprintf('scheme-%s', $tenantIndex), sprintf('%s taxonomy', $tenant), 'en_US'));
            $manager->persist($first);
            $manager->persist($second);
            $manager->persist(TagSynonymEntity::create(sprintf('synonym-%s', $tenantIndex), $first, 'featured'));
            $manager->persist(TagRelationEntity::create(sprintf('relation-%s', $tenantIndex), $first, $second, 'related'));
            $manager->persist(TagRedirectEntity::create(sprintf('redirect-%s', $tenantIndex), sprintf('old-%s', $second->slug()), $second));
            $manager->persist(TagAssignmentEntity::create($tenant, sprintf('assignment-%s', $tenantIndex), $second->id(), 'product', sprintf('product-%s', $tenantIndex)));
            $manager->persist(new TagProposalEntity(
                $tenant,
                sprintf('proposal-%s', $tenantIndex),
                'merge',
                ['from' => $first->slug(), 'to' => $second->slug(), 'reason' => 'Demo taxonomy cleanup'],
            ));
        }

        $manager->flush();
    }
}
