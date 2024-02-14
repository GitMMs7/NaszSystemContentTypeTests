<?php

namespace SCTeam\ContentTypes\Tests\Traits;

use Faker\Factory;
use Laravel\Dusk\Browser;
use SCTeam\Base\SCTeam;
use SCTeam\ContentTypes\Models\ContentType;
use SCTeam\ContentTypes\SCTeamServiceProvider;
use Illuminate\Support\Facades\DB;
use SoftDeletes;


trait ContentTypeTest
{

    public function newContentTypeItemName(Browser $browser, $nameFaker): array
    {
        //$faker = Factory::create();
        $browser->screenshot('newContentTypeItemName' . now()->format('Y-m-d H:i:s'));
        $browser->click('button.nav-link > i.icon-content-types')->waitFor(null, 7); // naciskam przycisk który jest linkiem o treści typy treści $this->lang(['pl'][1])); // naciskam przycisk który jest linkiem o treści typy treści

        $browser->screenshot('newContentTypeItemName2' . now()->format('Y-m-d H:i:s'));
        $browser->clickAndWaitForReload('.nav-sidebar a[href$="/admin/content-types"]', 15); // naciskam przycisk który jest linkiem o treści typy treści $this->lang(['pl'][1])); // naciskam przycisk który jest linkiem o treści typy treści
        $browser->clickAndWaitForReload('div > a[href$="/admin/content-types/create"]'); // naciskam przycisk który jest linkiem o treści typy treści $this->lang(['pl'][1])); // naciskam przycisk który jest linkiem o treści typy treści $this->lang(['pl'][4])); //'Dodaj nowy'// naciskam przycisk który jest linkiem o treści dodaj nowy
        $browser->type('pl[plural_label]', $nameFaker[0][0]); //'pl[plural_label]'//wprowadzam w pole formularza nazwę typu treści $this->lang(['pl'][6]), $nameFaker[0][0]); //'pl[plural_label]'//wprowadzam w pole formularza nazwę typu treści
        $browser->type('pl[label]', $nameFaker[0][1]); //'pl[plural_label]'//wprowadzam w pole formularza nazwę typu treści $this->lang(['pl'][7]), $nameFaker[0][1]); //'input[name="pl[label]"]'//wprowadzam w pole formularza nazwę typu treści w liczbie pojedynczej $this->lang(['pl'][7]), $nameFaker[0][1]); //'input[name="pl[label]"]'//wprowadzam w pole formularza nazwę typu treści w liczbie pojed

        return array("plural_label" => $nameFaker[0][0], "label" => $nameFaker[0][1]);
    }

    public function newContentTypeItemResult(browser $browser, $nameFaker): array
    {

        //var_dump($nameFaker);
        $browser->screenshot('newContentTypeItemResult1' . now()->format('Y-m-d H:i:s'));
        sleep(10);
        $browser->press('button.btn.btn-secondary.btn-sm.text-white.submit-dynamic-btn');//'Zapisz'
        $browser->screenshot('newContentTypeItemResult2' . now()->format('Y-m-d H:i:s'));
        //$browser->waitFor('li.breadcrumb-item.active2'); //'Edytuj'// czekam na tekst lista wszystkich typów treści
        sleep(10);
        $browser->clickAndWaitForReload( '.nav-sidebar a[href$="/admin/content-types"]'); // naciskam przycisk który jest linkiem o treści typy treści
        sleep(10);
        $id = \SCTeam\ContentTypes\Models\ContentType::get()->last()->id;
        $label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->label;
        $plural_label = \SCTeam\ContentTypes\Models\ContentTypeTranslation::where(['locale' => 'pl', 'content_type_id' => $id])->get()->last()->plural_label;
        try {
            $this->assertDatabaseHas('content_types', ['id' => $id]); // sprawdzam, czy na liście typów treści jest nazwa typu treści
        } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
            $this->assertFalse(true, "brak w bazie danych rekordu o id: $id");
        }
        if(!empty($nameFaker[0][0]))
        {
            try {
                $this->assertDatabaseHas('content_type_translations', ['plural_label' => $nameFaker[0][0]]); // sprawdzam czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie tytuł treści o nazwieL: ".$nameFaker[0][0]." nie udało się");
            }
        }
        if(!empty($nameFaker[0][1]) )
        {
            try {
                $this->assertDatabaseHas('content_type_translations', ['label' => $nameFaker[0][1]]); // sprawdzam czy na liście typów treści jest nazwa typu treści
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
                $this->assertFalse(true, "dodanie pojedynczego tytuł treści o nazwiePL: ".$nameFaker[0][1]." nie udało się");
            }
        }
        return array($id, $label, $plural_label);
    }

}