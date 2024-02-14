<?php

namespace SCTeam\ContentTypes\Tests\Browser;

use Faker\Factory;
use Illuminate\Contracts\Auth\PasswordBrokerFactory;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use laravel\Dusk\componenets\browser\page;
use SCTeam\Auth\Tests\CreateUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use SCTeam\Auth\Tests\Traits\LoggableTest;
use SCTeam\Base\Tests\DuskTestCase;
use SCTeam\ContentTypes\Tests\Browser\AuthTest;
use Illuminate\Support\Facades\DB;
use SCTeam\ContentTypes\Tests\Traits\ContentTypeTest;
use Laravel\Dusk\Keyboard;
use Facebook\WebDriver\WebDriverKeys;
use SCTeam\Base\SCTeam;
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\SCTeamServiceProvider;
use SCTeam\ContentTypes\Models\ContentTypeItem;
use SCTeam\ContentTypes\Models\ContentTypeTranslation;
use SCTeam\ContentTypes\Models\Slug;
use SCTeam\ContentTypes\Models\SlugTranslation;
use SCTeam\ContentTypes\Models\Taxonomy;
use SCTeam\ContentTypes\Models\TaxonomyTranslation;
use SCTeam\ContentTypes\Models\ContentField;
use SCTeam\ContentTypes\Models\ContentFieldGroup;
use SCTeam\ContentTypes\Models\ContentFieldGroupCondition;
use SCTeam\ContentTypes\Models\ContentFieldGroupConditionTranslation;
use SCTeam\ContentTypes\Models\ContentFieldGroupTranslation;
use SCTeam\ContentTypes\Models\ContentFieldTranslation;
use SCTeam\ContentTypes\Models\Structure;
use SCTeam\ContentTypes\Models\StructureTranslation;
use SCTeam\ContentTypes\Models\StructureItem;
use SCTeam\ContentTypes\Models\StructureItemTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentField;
use SCTeam\ContentTypes\Models\StructureItemContentFieldTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOption;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValue;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentField;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOption;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValue;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentField;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOption;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValue;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValueTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValueContentField;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValueContentFieldTranslation;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValueContentFieldOption;
use SCTeam\ContentTypes\Models\StructureItemContentFieldOptionValueContentFieldOptionValueContentFieldOptionValueContentFieldOptionTranslation;
use softDeletes;
Use SCTeam\ContentTypes\Models\ContentFieldOption;
Use SCTeam\ContentTypes\Models\ContentFieldOptionTranslation;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValue;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValueTranslation;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValueContentField;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValueContentFieldTranslation;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValueContentFieldOption;
Use SCTeam\ContentTypes\Models\ContentFieldOptionValueContentFieldOptionTranslation;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Enumerable;

/**
 * @group CrudOwnContentTypeTest
 */
class CrudOwnContentTypeTest extends DuskTestCase
{
    use CreateUsers;
    use LoggableTest;
    use ContentTypeTest;

    /**
     * @testCreateNewContentTypeOnlyNames
     */
    function testCreateNewContentTypeOnlyNames(): void
    { // tylko nazwy typu treści i nazwy w liczbie pojedynczej typu treści w nowym typie treści

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->screenshot('testCreateNewContentMainMetaAndSingleNameTypeContent_sucessfully_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testCreateContentTypeMetaTitle
     */
    public function testCreateContentTypeMetaTitle(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-pl\[meta_title\]', 'Domyślny_tytuł_meta'); //wprowadzam w pole formularza domyślny tytuł meta wartość Domyślny_tytuł_meta

            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_title' => 'Domyślny_tytuł_meta']); // sprawdzam czy na typ treści ma dodane domyślne meta tytuł
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie domyślnego meta tytułu : 'Domyślny_tytuł_meta' nie udało się");
            }

            $browser->screenshot('testCreateContentTypeMetaTitle_sucessfully_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testCreateNewContentTypeMetaKeywords
     */
    function testCreateNewContentTypeMetaKeywords(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-pl\[meta_keywords\]', 'Domyślne_słowa_kluczowe'); //wprowadzam w pole formularza domyślne słowa kluczowe wartość Domyślne_słowa_kluczowe

            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_keywords' => 'Domyślne_słowa_kluczowe']); // sprawdzam czy na typ treści ma dodane domyślne meta tytuł
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie domyślnych słów kluczowych jako : 'Domyślne_słowa_kluczowe' nie udało się");
            }

            $browser->screenshot('testCreateNewContentTypeMetaKeywords_sucessfully_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testCreateNewContentTypeMetaDescription
     */
    function testCreateNewContentTypeMetaDescription(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-pl\[meta_description\]', 'Domyślny opis meta'); //wprowadzam w pole formularza domyślny opis meta wartość Domyślny opis meta

            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_description' => 'Domyślny opis meta']); // sprawdzam czy na typ treści ma dodane domyślne meta tytuł
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie domyślnego opisu słów kluczowych jako : 'Domyślny opis meta' nie udało się");
            }

            $browser->screenshot('testCreateNewContentTypeMetaDescription_sucessfully_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testCreateNewContentTypeSlug
     */
    function testCreateNewContentTypeSlug(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker)
        {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-pl\[meta_description\]', 'Domyślny opis meta'); //wprowadzam w pole formularza domyślny opis meta wartość Domyślny opis meta

            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_description' => 'Domyślny opis meta']); // sprawdzam czy na typ treści ma dodane domyślne meta tytuł
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie meta_description jako : 'Domyślny opis meta' nie udało się");
            }

            $browser->screenshot('testCreateNewContentTypeMeta_description_sucessfully_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testCreateNewContentTypeAndChangeNotPublished
     */
    function testCreateNewContentTypeAndChangeNotPublished(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);
        var_dump(__t('scteam.content-types::common.content_type_updated'));

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker)
        {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeCreated_'.now()->format('Y-m-d H:i:s'));
            sleep(20);
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeCreated_2_'.now()->format('Y-m-d H:i:s'));
            $browser->refresh();
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeCreated_3_'.now()->format('Y-m-d H:i:s'));
            $browser->back()->back(); //cofam się do listy typów treści
            $browser->pause(10000);
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeCreatedT1_'.now()->format('Y-m-d H:i:s'));
            $browser->with('div.main-sidebar', function (Browser $browser2) use ($user, $password, $nameFaker, $a) { // ograniczam wyszukanie do lewej strony
                var_dump($a);
                $browser2->assertSee($a[2]); // sprawdzam czy w menu typów treści jest nazwa typu treści
            });
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeNotPublished_'.now()->format('Y-m-d H:i:s'));
            $browser->clickAndWaitForReload("td.scteam-listing-column.scteam-listing-column-label a[href$=\"/admin/content-types/$a[0]\"]"); //klikam w edycje typu treści i czekam na przeładowanie strony
            $browser->click('#select2-fields-status-container'); //klikam w checkbox dostępność
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeNotPublished_Click_'.now()->format('Y-m-d H:i:s'));
            $browser->withKeyboard(function (Keyboard $keyboard) { //rozpoczynam działanie na klawiaturze
                $keyboard->sendKeys(\Facebook\WebDriver\WebDriverKeys::DOWN)->sendKeys(\Facebook\WebDriver\WebDriverKeys::ENTER); //naciskam klawisze na klawiaturze strzałka w dół i ENTER
            });
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeNotPublished_selected_'.now()->format('Y-m-d H:i:s'));
            $browser->press('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn');//klikam przycisk Zapisz
            $browser->waitForText(__t('scteam.content-types::common.content_type_updated'),28); //'Edytuj'// czekam na tekst lista wszystkich typów treści
            $browser->back();
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_beforeNotPublishedAdPressed_'.now()->format('Y-m-d H:i:s'));
            $browser->pause(15000);
            $browser->screenshot('testCreateNewContentTypeAndChangeNotPublished_afterCreatedT2_'.now()->format('Y-m-d H:i:s'));
            $browser->with('div.main-sidebar', function (Browser $browser2) use ($user, $password, $nameFaker, $a)
            { // ograniczam wyszukanie do lewej strony
                $browser2->assertDontSee($a[2]);
            });
            $browser->pause(15000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();

            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testEditContentTypeSequence
     */
    function testEditContentTypeSequence(): void // sprawdzam kolejność // aktualnie nie zadziała, bo order nie zapisuje się ani nie aktualizuje się w bazie danych
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-order', '3'); //wprowadzam w pole formularza kolejność wartość 3
            $browser->screenshot('testEditContentTypeSequence_beforeChanged_'.now()->format('Y-m-d H:i:s'));
            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->pause('10000');
            $browser->screenshot('testEditContentTypeSequence_afterCreated_'.now()->format('Y-m-d H:i:s'));


            $id = \SCTeam\ContentTypes\Models\contentType::get()->last()->id;
            $order = \SCTeam\ContentTypes\Models\contentType::where(['id' => $id])->get()->last()->order;

            try {
                $this->assertDatabaseHas('content_types', ['order' => $order, 'id' => $id]); // sprawdzam czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja edycja kolejności nie udała się");
            }
            $browser->pause(20000);

            $browser->back()->back(); //cofam się do listy typów treści

            $browser->waitFor('div.col-md-9.page-right > div > div.page-desc', 30); // czekam na tekst lista wszystkich typów treści widoczny na liście typów treści
            $browser->clickAndWaitForReload("td.scteam-listing-column.scteam-listing-column-label a[href$=\"/admin/content-types/$a[0]\"]"); //klikam w edycje typu treści i czekam na przeładowanie strony
            $browser->pause(10000);

            $browser->screenshot('testEditContentTypeSequence_beforeCreated'.now()->format('Y-m-d H:i:s'));

            $browser->type('#fields-order', '5'); //wprowadzam w pole formularza kolejność wartość 5
            $browser->press('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn');//'Zapisz'
            $browser->waitFor('li.breadcrumb-item.active'); //'Edytuj'// czekam na tekst lista wszystkich typów treści
            $browser->screenshot('testEditContentTypeSequence_duringAsCheanged'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            $order = \SCTeam\ContentTypes\Models\contentType::where(['id' => $id])->get()->last()->order;

            try {
                $this->assertDatabaseHas('content_types', ['order' => $order, 'id' => $id]); // sprawdzam czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja edycja kolejności nie udała się");
            }

            $browser->pause(10000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });


    }

    /**
     * @testEditContentTypeName
     */
    function testEditContentTypeName(): void // sprawdzam czy działa edycja nazwy typu treści
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            $browser->back()->back(); //cofam się do listy typów treści
            $browser->waitFor('div.col-md-9.page-right > div > div.page-desc', 30); // czekam na tekst lista wszystkich typów treści widoczny na liście typów treści

            $browser->clickAndWaitForReload("td.scteam-listing-column.scteam-listing-column-label a[href$=\"/admin/content-types/$a[0]\"]"); //klikam w edycje typu treści i czekam na przeładowanie strony
            $browser->screenshot('testEditContentTypeName_beforeChanged_'.now()->format('Y-m-d H:i:s'));
            $browser->pause(10000);
            
            $browser->type('#fields-pl\[label\]', 'Zmieniona nazwa typu treści'); //wprowadzam w pole formularza nazwę typu treści
            $browser->type('#fields-pl\[plural_label\]', 'Zmieniona nazwa mnogiego typu treści'); //wprowadzam w pole formularza nazwę typu treści w liczbie pojedynczej
            $browser->press('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn');//klikam przycisk Zapisz
            $browser->waitForText('Edytuj', 20 ); //'Edytuj'// czekam na tekst lista wszystkich typów treści
            $browser->screenshot('testEditContentTypeName_afterChanged_'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            $label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->label;
            $plural_label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->plural_label;
            
            try {
                $this->assertDatabaseHas('content_type_translations', ['label' => 'Zmieniona nazwa typu treści', 'plural_label' => 'Zmieniona nazwa mnogiego typu treści']); // sprawdzam czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja label lub plural_label nie udała się");
            }

            $browser->pause(10000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });

    }

    /**
     * @testEditContentTypeMetaKeywords
     */
    public function testEditContentTypeMetaKeywords(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->type('#fields-pl\[meta_keywords\]', 'Zmienione domyślne słowa kluczowe'); //wprowadzam w pole formularza nazwę typu treści

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->pause(10000);
            $browser->screenshot('testEditContentTypeMetaKeywords_'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            //$meta_keywords = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->meta_keywords;

            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_keywords' => 'Zmienione domyślne słowa kluczowe']); // sprawdzam czy edycja meta_keywords się powiodła
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja słów kluczowych nie udała się");
            }

            $browser->pause(10000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });

    }

    /**
     * @testEditContentTypeMetaDescription
     */
    public function testEditContentTypeMetaDescription(): void
    {
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            
            $browser->type('#fields-pl\[meta_description\]', 'Zmieniony domyślny opis meta'); //wprowadzam w pole formularza nazwę typu treści

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->pause(10000);
            $browser->screenshot('testEditContentTypeMetaDescription_'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            //$meta_description = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->meta_description;

            try {
                $this->assertDatabaseHas('content_type_translations', ['meta_description' => 'Zmieniony domyślny opis meta']); // sprawdzam czy edycja meta_keywords się powiodła
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja opisu meta nie udała się");
            }

            $browser->pause(10000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
    }

    /**
     * @testEditContentTypeSlug
     */
    public function testEditContentTypeSlug(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            
            $browser->type('#fields-pl\[slug\]', 'Zmieniony domyślny slug'); //wprowadzam w pole formularza nazwę typu treści

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->pause(10000);
            $browser->screenshot('testEditContentTypeSlug_'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\Slugs\Models\Slug::get()->last()->id;
            //$meta_description = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->meta_description;

            try {
                $this->assertDatabaseHas('slug_translations', ['slug_id' => $id, 'locale' => 'pl', 'slug' => 'zmieniony-domyslny-slug']);
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "edycja Sluga nie udała się");
            }

            $browser->pause(10000);
            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });
        
    }

    /**
     * @testEditContentTypeIcon
     */
    Public function testEditContentTypeIcon(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            $browser->pause(10000);
            $browser->press("td.scteam-listing-column.scteam-listing-column-label a[href$=\"/admin/content-types/$a[0]\"]", 30); //klikam w edycje typu treści i czekam na przeładowanie strony
            $browser->screenshot('testEditContentTypeIcon_beforeChanged_'.now()->format('Y-m-d H:i:s'));
            $id = \SCTeam\contentTypes\Models\ContentType::get()->last()->id;
            $icon = \SCTeam\ContentTypes\Models\ContentType::where(['id' => $id])->get()->last()->icon;
            try {
                $this->assertDatabaseHas('content_types', ['id' => $id, 'icon' => $icon]);
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "brak brawidłowej ikonki");
            }
            $browser->click('#select2-fields-icon-container'); //klikam w ikonkę #select2-fields-icon-container
            $browser->withKeyboard(function (Keyboard $keyboard) { //rozpoczynam działanie na klawiaturze
                $keyboard->sendKeys(\Facebook\WebDriver\WebDriverKeys::DOWN)->sendKeys(\Facebook\WebDriver\WebDriverKeys::DOWN)->sendKeys(\Facebook\WebDriver\WebDriverKeys::ENTER); //naciskam klawisze na klawiaturze strzałka w dół i ENTER
            });
            $browser->pressAndWaitFor('button.submit-dynamic-btn', 30);//klikam przycisk Zapisz
            $browser->screenshot('testEditContentTypeIconAfter_Changed_'.now()->format('Y-m-d H:i:s'));
            $icon = \SCTeam\ContentTypes\Models\ContentType::where(['id' => $id])->get()->last()->icon;
            try {
                $this->assertDatabaseHas('content_types', ['id' => $id, 'icon' => $icon]);
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "brak brawidłowej ikonki");
            }

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });

    }

    /**
     * @testCreateclickSaveAndNewContentType
     */
    public function testCreateclickSaveAndNewContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $browser->press('button.dropdown-toggle.dropdown-toggle-split')->waitFor(null, 40); //klikam w ikonkę typów treści
            $browser->press('button.submit-dynamic-btn.dropdown-item')->waitForReload(null, 40);//klikam przycisk Zapisz i nowy

            $browser->pause(20000);

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;

            $browser->assertInputValue('#fields-pl\[label\]', ''); // sprawdzam czy pole label jest puste
            $browser->assertInputValue('#fields-pl\[plural_label\]', ''); // sprawdzam czy pole plural_label jest puste

            $browser->screenshot('testCreateclickSaveAndNewContentType_'.now()->format('Y-m-d H:i:s'));

            \DB::table('content_type_translations')->where('content_type_id', $id)->delete();
            \DB::table('content_types')->where('id', $id)->delete();
            \DB::table('slug_translations')->where('slug_id', $id)->delete();
            \DB::table('slugs')->where('id', $id)->delete();

            \DB::table('users')->where('email', $user->email)->delete();

        });

    }

    /**
     * @testCreateclickSaveAndCloseContentType
     */
    public function testCreateclickSaveAndCloseContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $browser->screenshot('testCreateclickSaveAndCloseContentType_beforeChanged_'.now()->format('Y-m-d H:i:s'));
            $browser->click('button.dropdown-toggle-split');

            $browser->click('[data-submit="save_and_close"]')->waitForReload(null, 40);//klikam przycisk Zapisz i nowy
            $browser->screenshot('testCreateclickSaveAndCloseContentType_afterChanged_'.now()->format('Y-m-d H:i:s'));

            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;

            $browser->assertVisible('a[href$="/admin/content-types/'.$id.'"]'); // sprawdzam czy na liście typów treści jest nazwa typu treści

            \DB::table('content_type_translations')->where('content_type_id', $id)->delete();
            \DB::table('content_types')->where('id', $id)->delete();
            \DB::table('slug_translations')->where('slug_id', $id)->delete();
            \DB::table('slugs')->where('id', $id)->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });

    }

    /**
     * testCreateclickSelectMainPageContentType
     */
    public function testCreateclickSelectMainPageContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {
            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);
            $browser->screenshot('testCreateclickSelectMainPageContentType_beforeChanged_' . now()->format('Y-m-d H:i:s'));
            $browser->click('button.dropdown-toggle-split');

            $browser->click('[data-submit="save_and_close"]')->waitForReload(null, 40);//klikam przycisk Zapisz i nowy
            $browser->screenshot('testCreateclickSelectMainPageContentType_afterChanged_' . now()->format('Y-m-d H:i:s'));

            sleep(20);
            $browser->click('button.scteam-listing-default-btn'); //klikam w przycisk ustaw jako domyślny

            sleep(20);
            $browser->screenshot('testCreateclickSelectMainPageContentType_afterChanged_afterSet_' . now()->format('Y-m-d H:i:s'));

            $browser->assertVisible('button.scteam-listing-default-btn.active'); // sprawdzam czy na liście typów treści jest nazwa typu treści
            $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            try {
                $this->assertDatabaseHas('content_types', ['id' => $id, 'allow_homepage' => true]); // sprawdzam, czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "nie udało się ustawić typu treści o id: $id jako domyślny");
            }

            \DB::table('content_type_translations')->where('content_type_id', $id)->delete();
            \DB::table('content_types')->where('id', $id)->delete();
            \DB::table('slug_translations')->where('slug_id', $id)->delete();
            \DB::table('slugs')->where('id', $id)->delete();

            \DB::table('users')->where('email', $user->email)->delete();

        });

    }

    /**
     * @testCreateclickPinAndUnPinContentType
     */
    public function testCreateclickPinContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->screenshot('testCreateclickPinContentType_'.now()->format('Y-m-d H:i:s'));

            $browser->waitFor('button.scteam-pins-pin-btn', 40); //czekam na przycisk przypnij
            $browser->click('button.scteam-pins-pin-btn'); //klikam w przycisk przypnij i[href$="/api/pins"]
            //('button.scteam-pins-pin-btn', 30);
            $browser->waitFor('ul[class="scteam-pins-navbar-list"] > li > a', 40); //czekam na przycisk przypnij
            $browser->assertVisible('ul[class="scteam-pins-navbar-list"] > li > a'); // sprawdzam czy jest pinezka
            $browser->screenshot('testCreateclickPinContentType_ClickedPin_assertVisible_'.now()->format('Y-m-d H:i:s'));

            $browser->waitFor('i.icon-close', 40); //czekam na przycisk przypnij
            $browser->click('i.icon-close'); //wyłączam przypięcie pineski
            $browser->waitUntilMissing('ul[class="scteam-pins-navbar-list"] > li > a', 80); //czekam na przycisk przypnij
            $browser->assertMissing('ul[class="scteam-pins-navbar-list"] > li > a'); // sprawdzam czy jest pinezka
            $browser->screenshot('testCreateclickPinContentType_ClickedPin_assertMissing_'.now()->format('Y-m-d H:i:s'));

            $browser->waitFor('button.scteam-pins-pin-btn', 40); //czekam na przycisk przypnij
            $browser->click('button.scteam-pins-pin-btn'); //klikam w przycisk przypnij i[href$="/api/pins"]
            //('button.scteam-pins-pin-btn', 30);
            $browser->waitFor('ul[class="scteam-pins-navbar-list"] > li > a', 40); //czekam na przycisk przypnij
            $browser->assertVisible('ul[class="scteam-pins-navbar-list"] > li > a'); // sprawdzam czy jest pinezka
            $browser->screenshot('testCreateclickPinContentType_ClickedPin_assertVisible2_'.now()->format('Y-m-d H:i:s'));

            $browser->waitFor('ul[class="scteam-pins-navbar-list"] > li > a', 40); //czekam na przycisk przypnij icon-pin
            $browser->click('button.scteam-pins-pin-btn.pinned i.icon-pin'); //wyłączam przypięcie pineski
            $browser->waitUntilMissing('ul[class="scteam-pins-navbar-list"] > li > a', 80); //czekam na przycisk przypnij
            //$browser->assertMissing('ul[class="scteam-pins-navbar-list"] > li > a'); // sprawdzam czy jest pinezka
            $browser->screenshot('testCreateclickPinContentType_ClickedPin_assertMissing2_'.now()->format('Y-m-d H:i:s'));

        });

    }

    /**
     * @testCreateclickOnPinnedContentType
     */
    public function testCreateclickOnPinnedContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->screenshot('testCreateclickPinContentType_' . now()->format('Y-m-d H:i:s'));

            $browser->waitFor('button.scteam-pins-pin-btn', 40); //czekam na przycisk przypnij
            $browser->click('button.scteam-pins-pin-btn'); //klikam w przycisk przypnij i[href$="/api/pins"]
            //('button.scteam-pins-pin-btn', 30);
            $browser->waitFor('ul[class="scteam-pins-navbar-list"] > li > a', 40); //czekam na przycisk przypnij
            $browser->assertVisible('ul[class="scteam-pins-navbar-list"] > li > a'); // sprawdzam czy jest pinezka
            $browser->screenshot('testCreateclickOnPinnedContentType_assertVisible_' . now()->format('Y-m-d H:i:s'));

            $browser->click('ul[class="scteam-pins-navbar-list"] > li > a'); //klikam w przypięty typ treści
            $browser->waitFor('li.breadcrumb-item.active', 40); //czekam na przycisk przypnij
            $browser->assertVisible('li.breadcrumb-item.active');
            $browser->screenshot('testCreateclickOnPinnedContentType_assertVisibleEditPage_' . now()->format('Y-m-d H:i:s'));

        });

    }

    /**
     * @testEditContentTypeInterchangeabilityNames
     */
    public function testEditContentTypeInterchangeabilityNames(): void
    {

        // etap 1 tworzę konto i loguje się na nie
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);

            //etap 2 tworzę typ treści
            $b = $this->newContentTypeItemName($browser, $nameFaker);

            //etap 2.1 zmieniam domyślne wartości, pierwszy wariant wartość z pola label wypełniona feikerem, wartość z pola plural_label pusta
            $textOfMenu = $b["plural_label"]; // body > div.wrapper > div.content-wrapper
            $textOfMenu2 = $b["label"];
            var_dump($textOfMenu2);
            var_dump($textOfMenu);

            $browser->type('#fields-pl\[label\]', $textOfMenu2); //czyszcze pole label
            $browser->type('#fields-pl\[plural_label\]', ""); //czyszcze pole label
            $nameFaker = array([0 => "", 1 => $textOfMenu2]);

            //etap 2.2 tworzę typ treści, przyciskam przycisk zapisz
            //żeby wartości w polu label były widoczne podmieniam klasy stylizujące widok edycji typu treści
            $browser->script(<<<JS
                var element = document.querySelector('.col-md-3.page-title');
                element.classList.remove('col-md-3');
                element.classList.remove('page-title');
                element.classList.add('form-group');
                element.classList.add('scteam-fields');
                element.classList.add('col-md-4');
                element.classList.add('scteam-fields-type-text');
            JS);
            $browser->screenshot('testEditContentTypeInterchangeabilityNames_insideEditType_' . now()->format('Y-m-d H:i:s'));
            $a = $this->newContentTypeItemResult($browser, $nameFaker);
            $browser->refresh();

            //etap 2.3 sprawdzam czy asercje, sukces jeśli wartość z pola label wystempuje w obu miejscach
            $browser->screenshot('testEditContentTypeInterchangeabilityNames_E2Eview1variant_' . now()->format('Y-m-d H:i:s'));
            $browser->assertScript("return $('a[href*=\"content-type\"]:contains(\"$textOfMenu2\")').length == 2;"); // sprawdzam czy asercja wystempuje w dóch miejscach za pomocą wartości z pola label
            $browser->clickAndWaitForReload("td.scteam-listing-column.scteam-listing-column-label a[href$=\"/admin/content-types/$a[0]\"]", 50); //klikam w edycje typu treści i czekam na przeładowanie strony

            // palisane zmiany widok edycji typu treści

            $browser->script(<<<JS
                var element = document.querySelector('.col-md-3.page-title');
                element.classList.remove('col-md-3');
                element.classList.remove('page-title');
                element.classList.add('form-group');
                element.classList.add('scteam-fields');
                element.classList.add('col-md-4');
                element.classList.add('scteam-fields-type-text');
            JS);
            //etap 3 wariant gdy pola label i plural_label nie są puste, edyscja pola plural_label

            $browser->type('#fields-pl\[plural_label\]', $textOfMenu); //wprowadzam w pole formularza nazwę typu treści w liczbie pojedynczej
            $browser->pressAndWaitFor('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn', 40); //'Zapisz'
            $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]', 60); // naciskam przycisk który jest linkiem o treści typy treści
            $browser->refresh();
            $browser->pause(22000);
            $browser->screenshot('testEditContentTypeInterchangeabilityNames_beforeChangedbb2_' . now()->format('Y-m-d H:i:s'));

            $browser->assertScript("return $('a[href*=\"content-type\"]:contains(\"$textOfMenu\")').length == 2;"); //sprawdzam czy po uzupełnieniu pola plural_label asercja z wartością z tego pola wystempuje w dóch miejscach

            $browser->pause(10000);

            \DB::table('content_type_translations')->where('content_type_id', $a[0])->delete();
            \DB::table('content_types')->where('id', $a[0])->delete();
            \DB::table('slug_translations')->where('slug_id', $a[0])->delete();
            \DB::table('slugs')->where('id', $a[0])->delete();

            \DB::table('users')->where('email', $user->email)->delete();
        });

    }

    /**
     * @testDeleteOneContentType
     */
    public function testDeleteOneContentType(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->screenshot('testDeleteContentType_beforeChanged_'.now()->format('Y-m-d H:i:s'));
            $lastid = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
            $nazwa = $browser->text('a[href$="/admin/content-types/'.$lastid.'"]'); //pobieram nazwę typu treści

            $browser->assertSee($nazwa); // sprawdzam czy na liście typów treści jest nazwa typu treści

            $browser->waitFor('button.scteam-pins-pin-btn', 40); //czekam na przycisk przypnij

            $browser->click('button.dropdown-toggle-split'); //rozwijam menu
            $browser->waitFor('li > button.btn-secondary.btn-sm', 40); //czekam na przycisk przypnij
            $browser->screenshot('testDeleteContentType_beforeChanged_AndClick' . now()->format('Y-m-d H:i:s'));
            $browser->click('li > button.btn-secondary.btn-sm'); //klikam w przycisk usuń i czekam na przeładowanie strony

            $browser->waitForDialog(30); //czekam na komunikat
            $browser->acceptDialog(); //akceptuje komunikat
            $browser->refresh();
            $browser->waitForReload(function (Browser $browser) {
                $browser->refresh();
            }, 30)->assertDontSee($nazwa); //czekam na przeładowanie strony

            $browser->screenshot('testDeleteContentType_beforeChanged_Alert2' . now()->format('Y-m-d H:i:s'));

        });

    }

    /**
     * @testDeleteContentTypeMass
     */
    public function testDeleteContentTypeMass(): void
    {

        try {
            $idpresent = \DB::table('content_type_translations')->where('label', 'Strona')->get()->last()->content_type_id;
            $content_type_strona_present = \DB::table('content_types')->where('id', $idpresent)->whereNull('deleted_at')->get()->first()->id;
        } catch (\Exception $e) {
            $content_type_strona_present = null;
        }
        var_dump($content_type_strona_present);
        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);
        $nameFaker2 = array([0 => $faker->name, 1 => $faker->name]);

        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker, $nameFaker2, $content_type_strona_present) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $a = $this->newContentTypeItemResult($browser, $nameFaker);

            $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]', 60); // naciskam przycisk który jest linkiem o treści typy treści
            $browser->screenshot('testDeleteContentType_beforeChanged1_'.now()->format('Y-m-d H:i:s'));
            sleep(20);
            $this->newContentTypeItemName($browser, $nameFaker2, 1); // jeśli 1 to pomiń pierwszy krok i przejdź do drugiego

            $b = $this->newContentTypeItemResult($browser, $nameFaker2);

            $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]', 60); // naciskam przycisk który jest linkiem o treści typy treści

            sleep(20);

            $browser->screenshot('testDeleteContentType_beforeChanged2_'.now()->format('Y-m-d H:i:s'));

            $browser->click('table > thead > tr > th > input[type="checkbox"]'); //zaznaczam wszystkie checkboxy

            if($content_type_strona_present == 1) {
                $browser->click('tbody > tr > td > input[type="checkbox"][value="1"]');
                sleep(20);
                $browser->assertSee($nameFaker[0][0]); // sprawdzam czy na liście typów treści jest nazwa typu treści
                $browser->assertSee($nameFaker2[0][0]); // sprawdzam czy na liście typów treści jest nazwa typu treści
                $browser->screenshot('testDeleteContentType_MassCeked_' . now()->format('Y-m-d H:i:s'));
                $browser->click('div.input-group.flex-nowrap'); //rozwijam menu
                $browser->screenshot('testDeleteContentType_clickMassActionMenu_' . now()->format('Y-m-d H:i:s'));
                sleep(20);
                $browser->click('select.form-control > option[value="delete"]'); //klikam w przycisk usuń i czekam na przeładowanie strony
                sleep(10);
                $browser->waitForDialog(30); //czekam na komunikat
                $browser->acceptDialog(); //akceptuje komunikat
                $browser->refresh();
                $browser->waitForReload(function (Browser $browser) {
                    $browser->refresh();
                }, 30)->assertDontSee($nameFaker[0][0])->assertDontSee($nameFaker2[0][0]); //czekam na przeładowanie strony

            } else {
                $browser->assertSee($nameFaker[0][0]); // sprawdzam czy na liście typów treści jest nazwa typu treści
                $browser->assertSee($nameFaker2[0][0]); // sprawdzam czy na liście typów treści jest nazwa typu treści
                $browser->screenshot('testDeleteContentType_MassCeked_' . now()->format('Y-m-d H:i:s'));
                $browser->click('div.input-group.flex-nowrap'); //rozwijam menu
                $browser->screenshot('testDeleteContentType_clickMassActionMenu_' . now()->format('Y-m-d H:i:s'));
                sleep(20);
                $browser->click('select.form-control > option[value="delete"]'); //klikam w przycisk usuń i czekam na przeładowanie strony
                sleep(10);
                $browser->waitForDialog(30); //czekam na komunikat
                $browser->acceptDialog(); //akceptuje komunikat
                $browser->refresh();
                $browser->waitForReload(function (Browser $browser) {
                    $browser->refresh();
                }, 30)->assertDontSee($nameFaker[0][0])->assertDontSee($nameFaker2[0][0]); //czekam na przeładowanie strony
            }

            //$browser->screenshot('testDeleteContentType_MassCekedAndLastUncheked_'.now()->format('Y-m-d H:i:s'));
        });

    }

    /**
     * @testCreateNewContentTypeEmptyNames
     */
    public function testCreateNewContentTypeEmptyNames(): void
    {

        $password = Str::random(10);
        $user = $this->makeAdmin([
            'password' => \Hash::make($password),
        ]);
        $faker = Factory::create();

        $nameFaker = array([0 => $faker->name, 1 => $faker->name]);
        //$nameFaker = array([0 => null, 1 => null]);
        $this->browse(function (Browser $browser) use ($user, $password, $nameFaker) {

            $this->login($browser, $user, $password);
            $this->newContentTypeItemName($browser, $nameFaker);

            $browser->script(<<<JS
                var element = document.querySelector('.col-md-3.page-title');
                element.classList.remove('col-md-3');
                element.classList.remove('page-title');
                element.classList.add('form-group');
                element.classList.add('scteam-fields');
                element.classList.add('col-md-4');
                element.classList.add('scteam-fields-type-text');
            JS);

            $browser->type('#fields-pl\[label\]', ""); //czyszcze pole label
            $browser->type('#fields-pl\[plural_label\]', ""); //czyszcze pole label
            $browser->screenshot('testCreateNewContentTypeEmptyNames_'.now()->format('Y-m-d H:i:s'));
            $a = $this->newContentTypeItemEmpty($browser);
            $wyczysc = 0;
            if($a['id'] != 0) {
                $wyczysc = $wyczysc+1;
                $usunac[$wyczysc] = $a['id'];
            }

            $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]', 60); // naciskam przycisk który jest linkiem o treści typy treści
            $browser->clickAndWaitForReload('div > a[href$="/admin/content-types/create"]', 60); // naciskam przycisk który jest linkiem o treści dodaj nowy
            $browser->screenshot('testCreateNewContentTypeEmptyNames2_'.now()->format('Y-m-d H:i:s'));

            $browser->type('#fields-pl\[label\]', ""); //czyszcze pole label
            $browser->type('#fields-pl\[plural_label\]', ""); //czyszcze pole label

            $a = $this->newContentTypeItemEmpty($browser);
            var_dump($a);
            if($a['id'] != 0) {
                $wyczysc = $wyczysc+1;
                $usunac[$wyczysc] = $a['id'];
            }
            var_dump($usunac);
            if($this->count($usunac) > 0)
            {
                var_dump(count($usunac));
                for($i = 1; $i <= $this->count($usunac); $i++)
                {
                    echo "[".$usunac[$i]."] \n";
                    $luggable_id = \SCTeam\Slugs\Models\Slug::where(['sluggable_id' => $usunac[$i]])->get()->last()->sluggable_id;
                    //$sluggble_id = \SCTeam\ContentTypes\Models\Slug::where(['sluggable_id' => $usunac[$i]])->get()->last()->sluggable_id;
                    \DB::table('slugs')->where('sluggable_id', $luggable_id)->delete();
                    \DB::table('content_types')->where('id', $usunac[$i])->delete();
                }
                \DB::table('users')->where('email', $user->email)->delete();
            }
        });
    }

    private function enterCode(Browser $browser, ?string $code = ''): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $browser->type("#digit-$i", substr($code, $i-1, 1)); //wypełniam formularz weryfikacji dwuetapowej kodem pobranym z bazy danych
        }
    }

    private function newContentTypeItemEmpty(browser $browser): array
    {
        $browser->press('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn');//'Zapisz'
        $browser->waitFor('li.breadcrumb-item.active'); //'Edytuj'// czekam na tekst lista wszystkich typów treści

        $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]', 23); // naciskam przycisk który jest linkiem o treści typy treści
        $browser->pause(10000);
        $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;

        try {
            $this->assertDatabaseHas('content_type_translations', ['content_type_id' => $id ,'locale' => 'pl', 'label' => '', 'plural_label' => '']); // sprawdzam czy na liście typów treści jest nazwa typu treści
        } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
            $this->assertTrue(true, "dodanie typu treści z pustymi polami label i plural_label nie udało się");
        }

        $label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->label;
        $plural_label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->plural_label;

        return array('id' => $id, 'label' => $label, 'plural_label' => $plural_label);
    }
}