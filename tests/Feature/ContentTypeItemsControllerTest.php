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
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\Models\ContentTypeItem;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Models\TaxonomyItem;
use SCTeam\ContentTypes\Tests\TestCase;

class ContentTypeItemsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use CreateUsers;

    public function testIndex()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $contentType1 = ContentType::factory()->create();
        $faker = Factory::create();
        $title = $faker->words(10, true);
        $otherTitle = $faker->words(10, true);
        $locale = app()->currentLocale();
        $contentType1Item1 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType1->getKey(),
            $locale => [
                'title' => "$title 1",
            ]
        ]);
        $contentType1Item2 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType1->getKey(),
            $locale => [
                'title' => "$title 2",
            ]
        ]);
        $contentType1Item3 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType1->getKey(),
            $locale => [
                'title' => $otherTitle,
            ]
        ]);
        $contentType2 = ContentType::factory()->create();
        $contentType2Item1 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType2->getKey(),
            $locale => [
                'title' => "$title 3",
            ]
        ]);
        $contentType2Item2 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType2->getKey(),
            $locale => [
                'title' => "$title 4",
            ]
        ]);
        $params = ['contentType' => $contentType1, 'search' => $title];

        $response = $this->get(route('content-types.content-type.index', $params));

        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString($title, $content);
        $this->assertStringContainsString("$title 1", $content);
        $this->assertStringContainsString("$title 2", $content);
        $this->assertStringNotContainsString("$title 3", $content);
        $this->assertStringNotContainsString("$title 4", $content);
        $this->assertStringNotContainsString($otherTitle, $content);
    }

    public function testCreateWithTaxonomies()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);

        $contentType1 = ContentType::factory()->create();
        $contentType2 = ContentType::factory()->create();
        $taxonomy1 = Taxonomy::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $taxonomy1Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 1",
            ]
        ]);
        $taxonomy1Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 2",
            ]
        ]);
        $taxonomy2 = Taxonomy::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $taxonomy2Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 3",
            ]
        ]);
        $taxonomy2Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 4",
            ]
        ]);
        $taxonomy3 = Taxonomy::factory()->create([
            'content_type_id' => $contentType2->getKey(),
        ]);
        $taxonomy3Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy3->getKey(),
            $locale => [
                'title' => "$title 5",
            ]
        ]);
        $taxonomy3Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy3->getKey(),
            $locale => [
                'title' => "$title 6",
            ]
        ]);
        $response = $this->get(route('content-types.content-type.create', $contentType1));
        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString("name=\"taxonomies[{$taxonomy1->getKey()}]\"", $content);
        $this->assertStringContainsString("name=\"taxonomies[{$taxonomy2->getKey()}]\"", $content);
        $this->assertStringNotContainsString("name=\"taxonomies[{$taxonomy3->getKey()}]\"", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy1Item1->getKey()}\">$title 1</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy1Item2->getKey()}\">$title 2</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy2Item1->getKey()}\">$title 3</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy2Item2->getKey()}\">$title 4</option>", $content);
        $this->assertStringNotContainsString("<option value=\"{$taxonomy3Item1->getKey()}\">$title 5</option>", $content);
        $this->assertStringNotContainsString("<option value=\"{$taxonomy3Item2->getKey()}\">$title 6</option>", $content);
    }

    public function testUpdateWithTaxonomies()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $locale = app()->currentLocale();
        $faker = Factory::create();
        $title = $faker->words(2, true);

        $contentType1 = ContentType::factory()->create();
        $contentType2 = ContentType::factory()->create();
        $taxonomy1 = Taxonomy::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $taxonomy1Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 1",
            ]
        ]);
        $taxonomy1Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy1->getKey(),
            $locale => [
                'title' => "$title 2",
            ]
        ]);
        $taxonomy2 = Taxonomy::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $taxonomy2Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 3",
            ]
        ]);
        $taxonomy2Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy2->getKey(),
            $locale => [
                'title' => "$title 4",
            ]
        ]);
        $taxonomy3 = Taxonomy::factory()->create([
            'content_type_id' => $contentType2->getKey(),
        ]);
        $taxonomy3Item1 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy3->getKey(),
            $locale => [
                'title' => "$title 5",
            ]
        ]);
        $taxonomy3Item2 = TaxonomyItem::factory()->create([
            'taxonomy_id' => $taxonomy3->getKey(),
            $locale => [
                'title' => "$title 6",
            ]
        ]);
        $contentTypeItem1 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $contentTypeItem1->taxonomyItems()->attach($taxonomy1Item1->getKey(), ['default' => true]);
        $contentTypeItem1->taxonomyItems()->attach($taxonomy2Item1->getKey());
        $contentTypeItem1->taxonomyItems()->attach($taxonomy2Item2->getKey(), ['default' => true]);

        $response = $this->get(route('content-types.content-type.show', $contentTypeItem1));
        $response->assertStatus(200);
        $content = $response->content();
        $this->assertStringContainsString("name=\"taxonomies[{$taxonomy1->getKey()}]\"", $content);
        $this->assertStringContainsString("name=\"taxonomies[{$taxonomy2->getKey()}]\"", $content);
        $this->assertStringNotContainsString("name=\"taxonomies[{$taxonomy3->getKey()}]\"", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy1Item1->getKey()}\" selected=\"selected\">$title 1</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy1Item2->getKey()}\">$title 2</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy2Item1->getKey()}\" selected=\"selected\">$title 3</option>", $content);
        $this->assertStringContainsString("<option value=\"{$taxonomy2Item2->getKey()}\" selected=\"selected\">$title 4</option>", $content);
        $this->assertStringNotContainsString("<option value=\"{$taxonomy3Item1->getKey()}\">$title 5</option>", $content);
        $this->assertStringNotContainsString("<option value=\"{$taxonomy3Item2->getKey()}\">$title 6</option>", $content);
        $this->assertStringContainsString(htmlentities("\"defaultSelected\":{$taxonomy1Item1->getKey()}"), $content);
        $this->assertStringNotContainsString(htmlentities("\"defaultSelected\":{$taxonomy2Item1->getKey()}"), $content);
        $this->assertStringContainsString(htmlentities("\"defaultSelected\":{$taxonomy2Item2->getKey()}"), $content);
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
            ]
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
            ]
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Embed,
            'config' => [
                'type' => ContentFieldType::Embed,
                "translations" => [$locale => ["label" => "Embed field"]],
                "default" => "https://youtube.com/12345",
                'source' => 'youtube',
            ]
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
        
        $contentType1 = ContentType::factory()->create();
        $contentType2 = ContentType::factory()->create();
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType1::class,
            'conditionable_id' => $contentType1->getKey(),
            'content_field_group_id' => $contentFieldGroup1->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType1::class,
            'conditionable_id' => $contentType1->getKey(),
            'content_field_group_id' => $contentFieldGroup2->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType2::class,
            'conditionable_id' => $contentType2->getKey(),
            'content_field_group_id' => $contentFieldGroup3->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType2::class,
            'conditionable_id' => $contentType2->getKey(),
            'content_field_group_id' => $contentFieldGroup4->getKey(),
        ]);
        $response = $this->get(route('content-types.content-type.create', $contentType1));
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
            ]
        ]);
        $contentField2 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Textarea,
            'config' => [
                'type' => ContentFieldType::Textarea,
                "translations" => [$locale => ["label" => "First textarea field"]],
                "default" => "Text example",
            ]
        ]);
        $contentField3 = ContentField::factory()->create([
            'content_field_group_id' => $contentFieldGroup1->getKey(),
            'type' => ContentFieldType::Embed,
            'config' => [
                'type' => ContentFieldType::Embed,
                "translations" => [$locale => ["label" => "Embed field"]],
                "default" => "https://url.com/12345",
                'source' => 'embed',
            ]
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

        $contentType1 = ContentType::factory()->create();
        $contentType2 = ContentType::factory()->create();
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType1::class,
            'conditionable_id' => $contentType1->getKey(),
            'content_field_group_id' => $contentFieldGroup1->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType2::class,
            'conditionable_id' => $contentType2->getKey(),
            'content_field_group_id' => $contentFieldGroup2->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType2::class,
            'conditionable_id' => $contentType2->getKey(),
            'content_field_group_id' => $contentFieldGroup3->getKey(),
        ]);
        ContentFieldGroupCondition::factory()->create([
            'conditionable_type' => $contentType2::class,
            'conditionable_id' => $contentType2->getKey(),
            'content_field_group_id' => $contentFieldGroup4->getKey(),
        ]);
        $contentTypeItem1 = ContentTypeItem::factory()->create([
            'content_type_id' => $contentType1->getKey(),
        ]);
        $value1 = $faker->words(2, true);
        ContentFieldValue::factory()->create([
            'content_field_id' => $contentField1->getKey(),
            'row_type' => $contentTypeItem1::class,
            'row_id' => $contentTypeItem1->getKey(),
            $locale => [
                'value' => $value1,
            ],
        ]);
        $value2 = $faker->url;
        ContentFieldValue::factory()->create([
            'content_field_id' => $contentField3->getKey(),
            'row_type' => $contentTypeItem1::class,
            'row_id' => $contentTypeItem1->getKey(),
            $locale => [
                'value' => $value2,
            ],
        ]);
        $response = $this->get(route('content-types.content-type.show', $contentTypeItem1));
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
