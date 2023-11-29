<?php

namespace App\Content\Select;

use Symfony\Component\Intl\Countries;

class CountryTypeSelect {
    /**
     * @return array<int, array{name: string, title: string}>
     */
    public function getValues(): array
    {
        $countries=Countries::getNames('fr');
        $data=[];
        foreach($countries as $code=>$country){
            $data[]=[
                'name'=>$code,
                'title'=>$country
            ];
        }

       return $data;
    }

    /**
     * Optional default value for a single select.
     */
    public function getSingleSelectDefaultValue()
    {
        return null;
    }

    /**
     * Optional default value for a multi select.
     *
     * @return array<int, array{name: string}>
     */
    public function getMultiSelectDefaultValue(): array
    {
        return [
            ['name' => null],
        ];
    }
}