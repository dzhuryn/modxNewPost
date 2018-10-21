<?php

class newPost
{
    /** @var $modx DocumentParser */
    private $modx;
    private $citiesTable;
    private $departmentTable;
    public function __construct($modx)
    {
        $this->modx = $modx;
        $this->citiesTable = $this->modx->getFullTableName('new_post_cities');
        $this->departmentTable = $this->modx->getFullTableName('new_post_departments');
    }

    public function updateCities($response){
        /** Ставим всем городам статус обновления 0*/
        $this->modx->db->update([
            'update_status'=>0
        ],$this->citiesTable);

        foreach ($response as $city){
            $cityId = $this->modx->db->getValue($this->modx->db->select('id',$this->citiesTable,'`city_ref` = "'.$this->modx->db->escape($city['city_ref']).'"'));
            $fields = [
                'city_ref'=>$city['city_ref'],
                'city'=>$city['city'],
                'city_ru'=>$city['cityRu'],
                'update_status'=>1
            ];
            $fields = $this->modx->db->escape($fields);

            if(empty($cityId)){
                $this->modx->db->insert($fields,$this->citiesTable);
            }
            else{
                $this->modx->db->update($fields,$this->citiesTable,'id = '.intval($cityId));
            }
        }
        //удаляем старие города в которых уже нет отделений
        $this->modx->db->delete($this->citiesTable,'update_status = 0');
    }
    public function updateDepartment($response){
        /** Ставим всем отделениям статус обновления 0*/
        $this->modx->db->update([
            'update_status'=>0
        ],$this->departmentTable);

        foreach ($response as $department){
            $departmentId = $this->modx->db->getValue($this->modx->db->select('id',$this->departmentTable,'`ref` = "'.$this->modx->db->escape($department['ref']).'"'));

            $fields = [
                'ref'=>$department['ref'],
                'city_ref'=>$department['city_ref'],
                'num'=>$department['number'],
                'warehouse_type'=>$department['warehouseType'],
                'warehouse_type_description'=>$department['warehouseTypeDescription'],
                'address'=>$department['address'],
                'address_ru'=>$department['addressRu'],
                'max_weight_allowed'=>$department['max_weight_allowed'],
                'update_status'=>1
            ];
            $fields = $this->modx->db->escape($fields);

            if(empty($departmentId)){
                $this->modx->db->insert($fields,$this->departmentTable);
            }
            else{
                $this->modx->db->update($fields,$this->departmentTable,'id = '.intval($departmentId));
            }
        }
        //удаляем старие города в которых уже нет отделений
        $this->modx->db->delete($this->departmentTable,'update_status = 0');
    }

    public function getCities($cityName,$lang)
    {
        $cityNameEscape = $this->modx->db->escape($cityName);
        $cityNameKey = $lang == 'ua'?'city':'city_ru';

        $where = '';
        $notIds = [];
        //для начала полное вхождение
        $q = $this->modx->db->select('*',$this->citiesTable,'`'.$cityNameKey.'` = "'.$cityNameEscape.'"','city  COLLATE  utf8_unicode_ci','40');
        $full = $this->modx->db->makeArray($q,'id');

        $notIds = array_column($full,'id');
        if(!empty($notIds)) $where = ' and id not in ('.implode(',',$notIds).')';

        //для частичное вхождение
        $q = $this->modx->db->select('*',$this->citiesTable,'`'.$cityNameKey.'` like "'.$cityNameEscape.'%"'.$where,'city  COLLATE  utf8_unicode_ci','40');
        $part = $this->modx->db->makeArray($q,'id');

        $notIds = array_merge($notIds,array_column($part,'id'));
        if(!empty($notIds))$where = ' and id not in ('.implode(',',$notIds).')';

        //like
        $q = $this->modx->db->select('*',$this->citiesTable,'`'.$cityNameKey.'` like "%'.$cityNameEscape.'%"'.$where,'city  COLLATE  utf8_unicode_ci','40');
        $like = $this->modx->db->makeArray($q,'id');

        $cities = array_merge($full,$part,$like);
        return json_encode(['results'=>$cities],JSON_UNESCAPED_UNICODE);
    }


    public function getDepartments($cityRef,$lang)
    {
        $cityRefEscape = $this->modx->db->escape($cityRef);


        $q = $this->modx->db->select('*',$this->departmentTable,'city_ref = "'.$cityRefEscape.'"','num asc');
        $res = $this->modx->db->makeArray($q);

        $data = [];
        foreach ($res as $department) {
            $department['title'] = $this->getDepartmentName($department,$lang);
            $data[] = $department;

        }
        return json_encode(['results'=>$data],JSON_UNESCAPED_UNICODE);
    }

    public function update($start)
    {
        $json = file_get_contents('https://novaposhta.ua/shop/office/getJsonWarehouseList');
        $json = json_decode($json, true);
        if (!empty($json['response'])) {
            $this->updateCities($json['response']);
            $this->updateDepartment($json['response']);

        $this->modx->logEvent(121,1,'Список отделений и городов новой почты обновлен.<br>Дата: '.date('d-m-Y H:i').'<br>Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.','newPostUpdate');
        }

    }

    private function getDepartmentName($department, $lang)
    {
        $addressKey = $lang=='ua'?'address':'address_ru';
        switch ($department['warehouse_type']) {
            case 'mini':
                $desc = $lang == 'ua' ? 'Поштомат' : 'Почтомат';
                break;
            case 'post':
            case 'cargo':
                $desc = $lang == 'ua' ? 'Відділення' : 'Отделение';
                break;
            case 'postomat':
                $desc = $lang == 'ua' ? 'Поштомат приватбанку' : 'Почтомат приватбанка';
                break;
        }

        $departmentName = $desc . ' №' . $department['num'];
        if (!empty($department['max_weight_allowed'])) {
            $departmentName .= ' (до ' . $department['max_weight_allowed'] . ' кг)';
        }
        $departmentName .= ', ' . $department[$addressKey];
        return $departmentName;
    }
}