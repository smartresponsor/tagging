<?php

declare(strict_types=1);

namespace App\Tagging\DataFixtures;

use App\DataFixtures\AbstractFakerFixture;
use App\Tagging\Entity\Tag\TagEntity;
use App\Tagging\Entity\Tag\TagEntityAssignment;
use App\Tagging\Entity\Tag\TagEntityClassification;
use App\Tagging\Entity\Tag\TagAssignmentEntity;
use App\Tagging\Entity\Tag\TagEntityPolicy;
use App\Tagging\Entity\Tag\TagEntityProposal;
use App\Tagging\Entity\Tag\TagEntityRelation;
use App\Tagging\Entity\Tag\TagEntityRedirect;
use App\Tagging\Entity\Tag\TagEntityScheme;
use App\Tagging\Entity\Tag\TagEntitySynonym;
use Doctrine\Persistence\ObjectManager;

final class TaggingDemoFixtures extends AbstractFakerFixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = $this->faker();
        $tenants = ['tenant-a', 'tenant-b'];

        foreach ($tenants as $tenantIndex => $tenant) {
            $policy = new TagPolicyEntity($tenant, [
                'visibility' => $tenantIndex % 2 === 0 ? 'public' : 'restricted',
                'approval' => ['required' => true, 'moderators' => ['team-tagging']],
            ]);
            $manager->persist($policy);

            $scheme = TagSchemeEntity::create($tenant, $faker->uuid(), sprintf('%s taxonomy', $tenant), $faker->randomElement(['en_US', 'uk_UA', null]));
            $manager->persist($scheme);

            $tags = [];
            foreach (range(1, 4) as $index) {
                $tag = TagEntity::create($tenant, $faker->bothify('tag-########'), sprintf('%s-%02d', $tenant, $index), $faker->words(2, true));
                if (0 === $index % 2) {
                    $tag->setFlags(true, 0 === $index % 4);
                }
                $manager->persist($tag);
                $tags[] = $tag;

                $manager->persist(TagSynonymEntity::create($tenant, $faker->uuid(), $tag->id(), $faker->word()));
                $manager->persist(new TagClassificationEntity(
                    $tenant,
                    $faker->uuid(),
                    $faker->randomElement(['product', 'vendor', 'page']),
                    $faker->bothify('ref-###'),
                    $faker->randomElement(['segment', 'topic', 'channel']),
                    $faker->randomElement(['hot', 'new', 'evergreen']),
                ));
            }

            for ($index = 1; $index < \count($tags); ++$index) {
                $manager->persist(TagRelationEntity::create($tenant, $faker->uuid(), $tags[$index - 1]->id(), $tags[$index]->id(), $index % 2 === 0 ? 'broader' : 'related'));
                $manager->persist(new TagRedirectEntity($tenant, sprintf('old-%s', $tags[$index]->slug()), $tags[$index]->id()));
                $manager->persist(TagAssignmentEntity::create($tenant, $faker->uuid(), $tags[$index]->id(), $faker->randomElement(['product', 'vendor', 'page']), $faker->bothify('entity-###')));
            }

            $manager->persist(new TagProposalEntity(
                $tenant,
                $faker->uuid(),
                'merge',
                [
                    'from' => $tags[0]->slug(),
                    'to' => $tags[1]->slug(),
                    'reason' => $faker->sentence(),
                ],
            ));
        }

        $manager->flush();
    }
}
