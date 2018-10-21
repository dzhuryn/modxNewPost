# Пакет для  выбора и обновления списка городов и отделений новой почты

## Установка
1. Установить пакет.
2. В крон добавить запуск скрипта по урл /newPost-update для обновления списка раз в день.
3. Перейти по адресу /newPost-update чтоб записать в базу список городов и отделений.

## Использование на сайте
1. Для начала необходимо подключить css и js от select2
    ```html
    <link rel="stylesheet" href="/assets/lib/select2/css/select2.min.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="/assets/lib/select2/js/select2.min.js"></script>
    ```
2. В форму добавить следующие поля:

    ```html
    <input type="hidden" id="cityName" name="cityName">
    <input type="hidden" id="departmentName" name="departmentName">
    <select name="city" id="new-post-city">
        <option value="0">Выберите город</option>
    </select>
    <select name="department" id="new-post-department">
        <option value="0">Выберите отделение</option>
    </select>
    ```    
    cityName - Название города  
    departmentName - Название отделения  
    city - код города в системе новой почты  
    department - код отделения в системе новой почты  

3. Добавить следующий javascript код.
```javascript
$(document).ready(function() {
     var newPostDepartmentObj = $('#new-post-department');

     var lang = 'ua';
     var suffix =  lang === 'ua'?'':'_ru';

     var cityIdKey = 'city_ref';
     var cityNameKey = 'city'+suffix;

     var citySelect2Obj = $('#new-post-city').select2({
         ajax: {
             url: "/newPost-getCities",
             dataType: 'json',
             delay: 250,
             minimumInputLength: 1,
             data: function (params) {
                 return {
                     query: params.term,
                     lang:lang
                 };
             },
             processResults: function (data, params) {
                 var newData = $.map(data.results, function(item, index){
                     item.id = item[cityIdKey];
                     item.text = item[cityNameKey];
                     return item;
                 });
                 return {
                     results: newData
                 };
             }
         }
     });
     var departmentSelect = newPostDepartmentObj.select2();

     citySelect2Obj.on("select2:select", function (e) {
         $('#cityName').val(e.params.data[cityNameKey]);
         $('#departmentName').val('');
         departmentSelect.html('')

         var cityRef = e.params.data[cityIdKey];
         $.get('/newPost-getDepartments',{
             city_ref:cityRef,
             lang:lang
         },function (data) {
             data = JSON.parse(data);
             $.map(data.results, function(item, index){
                 var option = new Option(item['title'], item['ref'], false, false);
                 departmentSelect.append(option).trigger('change');
             });
         });
     });
     departmentSelect.on("select2:select", function (e) {
         $('#departmentName').val(e.params.data['text']);
     })
     
 });
```
После начала ввода названия города отправляется запрос по урл `newPost-getCities` из вводом пользователя и языком.
После получения ответа получаем список городов.  
После выбора города из выпадающего списка, делаем запрос по урлу `newPost-getDepartments` для получения отделений конкретного города.
Если вам нужен руский язык в переменную lang надо написать 'ru'.   
Если у вас мультиязычный сайт необходимо менять значение в зависимости от выбора пользователя.
Название отделения формируется из номера, типа и улицы.


