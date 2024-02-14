<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\Base\Tests\UsesTranslations;
use SCTeam\ContentTypes\Http\Resources\ContentTypeResource;
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Tests\TestCase;
use SCTeam\ContentTypes\Enums\PublicationStatus;

class ContentTypesApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use UsesTranslations;
    use CreateUsers;

    public function testStore()
    {
        $admin = $this->makeAdmin();
        $factory = Factory::create();
        $random = \Str::random();
        $taxonomies = Taxonomy::factory(10)->create();
        $label = $factory->name;
        $data = [
            'status' => PublicationStatus::Published,
            ...$this->forEachLocale(fn($locale) => [
                'plural_label' => 'Trenerzy z bullerbyn ' . $random,
                'label' => $label,
                'slug' => '',
            ]),
            'is_archive_page' => false,
            'is_details_page' => false,
            'show_in_url' => true,
            'order' => 1,
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

        $response = $request->post(route('api.content-types.store'), $data);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.content_type_created', 'Content type created!'))
            ->where('success', true)
            ->has('redirect'),
        );

        $contentType = ContentType::latest()->first();
        $this->assertNotEmpty($contentType);
        $this->assertEquals($label, $contentType->label);
        $this->assertTrue($contentType->show_in_url);
        $this->assertEquals('trenerzy-z-bullerbyn-' . strtolower($random), $contentType->getSlug());
        $this->assertEquals(PublicationStatus::Published(), $contentType->status);
    }

    public function testUpdate()
    {
        $admin = $this->makeAdmin();
        $factory = Factory::create();

        $contentType = ContentType::factory()->create();
        $label = $factory->name;
        $random = \Str::random();

        $data = [
            'status' => PublicationStatus::Unpublished,
            ...$this->forEachLocale(fn($locale) => [
                'plural_label' => 'Customowy typ treści ' . $random,
                'label' => $label,
                'slug' => '',
            ]),
            'is_archive_page' => false,
            'is_details_page' => false,
            'show_in_url' => false,
            'order' => 11,
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
        $response = $request->post(route('api.content-types.update', $contentType), $data);

        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('message', __t('scteam.content-types::common.content_type_updated', 'Content type updated!'))
            ->where('success', true)
            ->where('data', (new ContentTypeResource($contentType->fresh()))->jsonSerialize()),
        );

        $contentType = $contentType->fresh();
        $this->assertNotEmpty($contentType);
        $this->assertEquals($label, $contentType->label);
        $this->assertEquals('customowy-typ-tresci-' . strtolower($random), $contentType->getSlug());
        $this->assertEquals(PublicationStatus::Unpublished(), $contentType->status);
    }
}