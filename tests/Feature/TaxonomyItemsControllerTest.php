<?php

namespace SCTeam\ContentTypes\Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SCTeam\Auth\Tests\CreateUsers;
use SCTeam\ContentFields\Enums\ContentFieldGroupPosition;
use SCTeam\ContentFields\Enums\ContentFieldType;
use SCTeam\ContentFields\Models\ContentField;
use SCTeam\ContentFields\Models\ContentFieldGroup;
use SCTeam\ContentFields\Models\ContentFieldGroupCondition;
use SCTeam\ContentFields\Models\ContentFieldValue;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Models\TaxonomyItem;
use SCTeam\ContentTypes\Tests\TestCase;

class TaxonomyItemsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use CreateUsers;

    public function testIndex()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $taxonomy1 = Taxonomy::factory()->create();
        $faker = Factory::create();
        $title = $faker->words(10, true);
        $otherTitle = $faker->words(10, true);
        $locale = app()->currentLocale();
        $taxonomy1Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 1",
            ],
        ]);
        $taxonomy1Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 2",
            ],
        ]);
        $taxonomy1Item3 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => $otherTitle,
            ],
        ]);
        $taxonomy2 = Taxonomy::factory()->create();
        $taxonomy2Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 3",
            ],
        ]);
        $taxonomy2Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 4",
            ],
        ]);
        $params = ['taxonomy' => $taxonomy1, 'search' => $title];

        $response = $this->get(route('content-types.taxonomy.index', $params));

        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString($title, $content);
        $this->assertStringContainsString("$title 1", $content);
        $this->assertStringContainsString("$title 2", $content);
        $this->assertStringNotContainsString("$title 3", $content);
        $this->assertStringNotContainsString("$title 4", $content);
        $this->assertStringNotContainsString($otherTitle, $content);
    }

    public function testCreateWithContentFields()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);

        $contentFieldGroup1 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Bottom,
            'has_conditions' => true,
        ]);
        $contentField1 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Text,
            'config' => [
                'type' => ContentFieldType::Text,
                "translations" => [$locale => ["label" => "First text field"]],
                'prepend' => 'prefix',
                'append' => 'sufix',
                "default" => "Text example",
            ],
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
            ],
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Embed,
            'config' => [
                'type' => ContentFieldType::Embed,
                "translations" => [$locale => ["label" => "Embed field"]],
                "default" => "https://youtube.com/12345",
                'source' => 'youtube',
            ],
        ]);

        $contentFieldGroup2 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => true,
        ]);
        $contentFieldGroup3 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::CustomTab,
            'has_conditions' => false,
        ]);
        $contentFieldGroup4 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => true,
        ]);

        $taxonomy1 = Taxonomy::factory()->create();
        $taxonomy2 = Taxonomy::factory()->create();
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy1::class,
            'conditionable_id' => $taxonomy1->getKey(),
            'content_field_group_id' => $contentFieldGroup1->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy1::class,
            'conditionable_id' => $taxonomy1->getKey(),
            'content_field_group_id' => $contentFieldGroup2->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy2::class,
            'conditionable_id' => $taxonomy2->getKey(),
            'content_field_group_id' => $contentFieldGroup3->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy2::class,
            'conditionable_id' => $taxonomy2->getKey(),
            'content_field_group_id' => $contentFieldGroup4->getKey(),
        ]);
        $response = $this->get(route('content-types.taxonomy.create', $taxonomy1));
        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString("<span>$contentFieldGroup1->name</span>", $content);
        $this->assertStringContainsString("<span>$contentFieldGroup2->name</span>", $content);
        $this->assertStringContainsString("<span>$contentFieldGroup3->name</span>", $content);
        $this->assertStringNotContainsString("<span>$contentFieldGroup4->name</span>", $content);
        $this->assertStringContainsString("type=\"text\" name=\"content_fields[$contentField3->name]\"", $content);
        $this->assertStringContainsString("name=\"content_fields[{$contentField1->name}]\" type=\"text\" value=\"Text example\"", $content);
        $this->assertMatchesRegularExpression("([<textarea].* [name=\"content_fields\[{$contentField2->name}\]\"].*[<\/textarea>])", $content);
    }

    public function testUpdateWithContentFields()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);

        $contentFieldGroup1 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Bottom,
            'has_conditions' => true,
        ]);
        $contentField1 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Text,
            'config' => [
                'type' => ContentFieldType::Text,
                "translations" => [$locale => ["label" => "First text field"]],
                'prepend' => 'prefix',
                'append' => 'sufix',
                "default" => "Text example",
            ],
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
            ],
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Embed,
            'config' => [
                'type' => ContentFieldType::Embed,
                "translations" => [$locale => ["label" => "Embed field"]],
                "default" => "https://url.com/12345",
                'source' => 'embed',
            ],
        ]);

        $contentFieldGroup2 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => true,
        ]);
        $contentFieldGroup3 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::CustomTab,
            'has_conditions' => false,
        ]);
        $contentFieldGroup4 = ContentFieldGroup::factory()->create([
            'position' => ContentFieldGroupPosition::Top,
            'has_conditions' => true,
        ]);

        $taxonomy1 = Taxonomy::factory()->create();
        $taxonomy2 = Taxonomy::factory()->create();
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy1::class,
            'conditionable_id' => $taxonomy1->getKey(),
            'content_field_group_id' => $contentFieldGroup1->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy2::class,
            'conditionable_id' => $taxonomy2->getKey(),
            'content_field_group_id' => $contentFieldGroup2->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy2::class,
            'conditionable_id' => $taxonomy2->getKey(),
            'content_field_group_id' => $contentFieldGroup3->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $taxonomy2::class,
            'conditionable_id' => $taxonomy2->getKey(),
            'content_field_group_id' => $contentFieldGroup4->getKey(),
        ]);
        $taxonomyItem1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
        ]);
        $value1 = $faker->words(2, true);
        ContentFieldValue::factory()->create([
            'content_field_id' => $contentField1->getKey(),
            'row_type' => $taxonomyItem1::class,
            'row_id' => $taxonomyItem1->getKey(),
            $locale => [
                'value' => $value1,
            ],
        ]);
        $value2 = $faker->url;
        ContentFieldValue::factory()->create([
            'content_field_id' => $contentField3->getKey(),
            'row_type' => $taxonomyItem1::class,
            'row_id' => $taxonomyItem1->getKey(),
            $locale => [
                'value' => $value2,
            ],
        ]);
        $response = $this->get(route('content-types.taxonomy.show', $taxonomyItem1));
        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString("<span>$contentFieldGroup1->name</span>", $content);
        $this->assertStringNotContainsString("<span>$contentFieldGroup2->name</span>", $content);
        $this->assertStringContainsString("<span>$contentFieldGroup3->name</span>", $content);
        $this->assertStringNotContainsString("<span>$contentFieldGroup4->name</span>", $content);
        $this->assertStringContainsString("type=\"text\" name=\"content_fields[$contentField3->name]\"", $content);
        $this->assertStringContainsString("name=\"content_fields[{$contentField1->name}]\" type=\"text\" value=\"$value1\"", $content);
        $this->assertStringNotContainsString("name=\"content_fields[{$contentField1->name}]\" type=\"text\" value=\"Text example\"", $content);
        $this->assertMatchesRegularExpression("([<textarea].* [name=\"content_fields\[{$contentField2->name}\]\"].*[<\/textarea>])", $content);
        $this->assertStringContainsString("id=\"fields-content_fields[$contentField3->name]\" value=\"$value2\"", $content);
    }
}
