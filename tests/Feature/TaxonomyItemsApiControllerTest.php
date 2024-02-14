<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\Base\Tests\UsesTranslations;
use SCTeam\ContentFields\Enums\ContentFieldGroupPosition;
use SCTeam\ContentFields\Enums\ContentFieldType;
use SCTeam\ContentFields\Models\ContentField;
use SCTeam\ContentFields\Models\ContentFieldGroup;
use SCTeam\ContentFields\Models\ContentFieldGroupCondition;
use SCTeam\ContentTypes\Http\Resources\TaxonomyItemResource;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Models\TaxonomyItem;
use SCTeam\ContentTypes\Tests\TestCase;
use SCTeam\ContentTypes\Enums\PublicationStatus;

class TaxonomyItemsApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use UsesTranslations;
    use CreateUsers;

    public function testStore()
    {
        $admin = $this->makeAdmin();
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);
        $random = \Str::random();
        $contentFieldGroup1 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => false,
        ]);
        $contentField1 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Number,
            'config' => [
                'type' => ContentFieldType::Number,
                "translations" => [$locale => ["label" => "First number field"]],
                "default" => 1,
            ]
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
                "translatable" => true,
            ]
        ]);
        $contentFieldGroup2 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::CustomTab,
            'has_conditions' => false,
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup2->getKey(),
            'type' => ContentFieldType::Email,
            'config' => [
                'type' => ContentFieldType::Email,
                "translations" => [$locale => ["label" => "First email field"]],
                "default" => null,
            ]
        ]);
        $taxonomy = Taxonomy::factory()->create();

        $email = $faker->email;
        $cptTitle = 'Testowy typ treści ' . $random;
        $data = [
            'status' => PublicationStatus::Published,
            ...$this->forEachLocale(fn($locale) => [
                'title' => $cptTitle,
                'meta_title' => 'Testowa taksonomia - meta tytuł' . $random,
            ]),
            'taxonomy_id' => $taxonomy->getKey(),
            "content_fields" => [
                $contentField1->name => 180,
                $contentField3->name => $email,
                $locale => [
                    $contentField2->name => "$title - value"
                ]
            ],
            'user_id' => $admin->getKey(),
            'rendered_dynamic_tabs' => ["base"],
            'submit' => 'save',
        ];
        if (class_exists("Laravel\\Passport\\Passport")) {
            $request = $this->flushHeaders();
            Passport::actingAs($admin);
        } else {
            $request = $this->withHeaders([
                'Authorization' => 'Bearer ' . $admin->api_token,
            ]);
        }

        $response = $request->post(route('api.taxonomy-items.store'), $data);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.new_item_successfully_created', 'New item successfully created!'))
            ->where('success', true)
            ->has('redirect'),
        );

        $taxonomyItem = TaxonomyItem::whereTaxonomyId($taxonomy->getKey())->latest()->first();
        $this->assertNotEmpty($taxonomyItem);
        $this->assertEquals($cptTitle, $taxonomyItem->title);
        $this->assertEquals(180, (int)\SCTeamContentFields::getValue($contentField1->name, $taxonomyItem::class, $taxonomyItem->getKey()));
        $this->assertEquals("$title - value", \SCTeamContentFields::getValue($contentField2->name, $taxonomyItem));
        $this->assertEquals($email, \SCTeamContentFields::getValue($contentField3->name, $taxonomyItem));
    }

    public function testUpdate()
    {
        $admin = $this->makeAdmin();
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);
        $random = \Str::random();
        $contentFieldGroup1 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => false,
        ]);
        $contentField1 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Number,
            'config' => [
                'type' => ContentFieldType::Number,
                "translations" => [$locale => ["label" => "First number field"]],
                "default" => 1,
            ]
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
                "translatable" => true,
            ]
        ]);
        $contentFieldGroup2 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::CustomTab,
            'has_conditions' => false,
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup2->getKey(),
            'type' => ContentFieldType::Email,
            'config' => [
                'type' => ContentFieldType::Email,
                "translations" => [$locale => ["label" => "First email field"]],
                "default" => null,
            ]
        ]);
        $taxonomy = Taxonomy::factory()->create();
        $taxonomyItem = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy->getKey(),
        ]);
        $email = $faker->email;
        $cptTitle = 'Testowa taksonomia ' . $random;
        $data = [
            'status' => PublicationStatus::Published,
            ...$this->forEachLocale(fn($locale) => [
                'title' => $cptTitle,
                'meta_title' => 'Testowa taksonomia - meta tytuł' . $random,
            ]),
            'taxonomy_id' => $taxonomy->getKey(),
            "content_fields" => [
                $contentField1->name => 120,
                $contentField3->name => $email,
                $locale => [
                    $contentField2->name => "$title - value"
                ]
            ],
            'user_id' => $admin->getKey(),
            'rendered_dynamic_tabs' => ["base"],
            'submit' => 'save',
        ];
        if (class_exists("Laravel\\Passport\\Passport")) {
            $request = $this->flushHeaders();
            Passport::actingAs($admin);
        } else {
            $request = $this->withHeaders([
                'Authorization' => 'Bearer ' . $admin->api_token,
            ]);
        }

        $response = $request->post(route('api.taxonomy-items.update', $taxonomyItem), $data);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.item_successfully_updated', 'Item successfully updated!'))
            ->where('success', true)
            ->where('data', (new TaxonomyItemResource($taxonomyItem->fresh()))->jsonSerialize()),
        );
        $taxonomyItem = $taxonomyItem->fresh();
        $this->assertEquals($cptTitle, $taxonomyItem->title);
        $this->assertEquals('Testowa taksonomia - meta tytuł' . $random, $taxonomyItem->meta_title);
        $this->assertEquals(120, (int)\SCTeamContentFields::getValue($contentField1->name, $taxonomyItem::class, $taxonomyItem->getKey()));
        $this->assertEquals("$title - value", \SCTeamContentFields::getValue($contentField2->name, $taxonomyItem));
        $this->assertEquals($email, \SCTeamContentFields::getValue($contentField3->name, $taxonomyItem));
    }
}