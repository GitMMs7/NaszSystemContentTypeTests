<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\Base\Tests\UsesTranslations;
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Tests\TestCase;
use SCTeam\ContentTypes\Enums\PublicationStatus;

class TaxonomiesApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use UsesTranslations;
    use CreateUsers;

    public function testStore()
    {
        $admin = $this->makeAdmin();
        $factory = Factory::create();
        $random = \Str::random();
        $label = $factory->name;
        $contentType = ContentType::factory()->create();
        $data = [
            'status' => PublicationStatus::Published,
            ...$this->forEachLocale(fn($locale) => [
                'plural_label' => 'Trenerzy z bullerbyn ' . $random,
                'label' => $label,
                'slug' => '',
            ]),
            'content_type_id' => $contentType->getKey(),
            'is_archive_page' => false,
            'show_in_url' => true,
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

        $response = $request->post(route('api.content-types.taxonomies.store'), $data);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.taxonomy_created', 'Taxonomy created!'))
            ->where('success', true)
            ->has('redirect'),
        );

        $taxonomy = Taxonomy::latest()->first();
        $this->assertNotEmpty($taxonomy);
        $this->assertEquals($label, $taxonomy->label);
        $this->assertTrue($taxonomy->show_in_url);
        $this->assertEquals($contentType->getKey(), $taxonomy->contentType->getKey());
        $this->assertEquals('trenerzy-z-bullerbyn-' . strtolower($random), $taxonomy->getSlug());
        $this->assertEquals(PublicationStatus::Published(), $taxonomy->status);
    }

    public function testUpdate()
    {
        $admin = $this->makeAdmin();
        $factory = Factory::create();

        $taxonomy = Taxonomy::factory()->create();
        $label = $factory->name;
        $random = \Str::random();

        $data = [
            'status' => PublicationStatus::Unpublished,
            ...$this->forEachLocale(fn($locale) => [
                'plural_label' => 'Customowa taxonomia ' . $random,
                'label' => $label,
                'slug' => '',
            ]),
            'is_archive_page' => false,
            'show_in_url' => false,
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
        $response = $request->post(route('api.content-types.taxonomies.update', $taxonomy), $data);

        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.taxonomy_updated', 'Taxonomy updated!'))
            ->where('success', true)
        );

        $taxonomy = $taxonomy->fresh();
        $this->assertNotEmpty($taxonomy);
        $this->assertEquals($label, $taxonomy->label);
        $this->assertEquals('customowa-taxonomia-' . strtolower($random), $taxonomy->getSlug());
        $this->assertEquals(PublicationStatus::Unpublished(), $taxonomy->status);
    }
}