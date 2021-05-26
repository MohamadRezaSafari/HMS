<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
/*
|--------------------------------------------------------------------------
| Access denied
|--------------------------------------------------------------------------
*/




Route::get('/cronJob/B3uk4nWu5c/checkSMSVisit/d522peEOA7/{hospitalId}', 'SmsController@cronJob')->where('id', '[0-9]+');


Route::get('/payHospital/{id}', 'PayController@payHospital')->where('id', '[0-9]+');
Route::get('/payPoliclinic/{id}', 'PayController@payPoliclinic')->where('id', '[0-9]+');



Route::get('/pay/redirect', function($data){
    dd($data);
});


Route::get('/AccessDenied', function (){
    return view('errors.access');
});
/*
|--------------------------------------------------------------------------
| Login - Register - Logout system
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['throttle:4:1']], function () {
    Auth::routes();
    Route::get('d56d985300d4b52eb6e189be006f44f8d23c5ec9', 'CheckController@checkUrl');
});


Route::group(['middleware' => ['throttle:3:1']], function () {
    /*
    |--------------------------------------------------------------------------
    | Doctor Queue
    |--------------------------------------------------------------------------
    */
    Route::post('/doctor-Queue', 'QueueController@doctorQueue');
    /*
    |--------------------------------------------------------------------------
    | Hospital Queue
    |--------------------------------------------------------------------------
    */
    Route::post('/hospital-Queue', 'QueueController@hospitalQueue');

});
/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/
Route::post('/searchAll', 'SearchController@searchAll');
Route::get('/search/trackingCode', 'SearchController@trackingCode');

Route::group(['middleware' => ['throttle:5:1']], function () {
    Route::post('/search/trackingCodeSend', 'SearchController@trackingCodeSend');
});

/*
|--------------------------------------------------------------------------
| Hospital(s) lists
|--------------------------------------------------------------------------
*/
Route::get('/hospitals', 'HospitalController@all');
Route::post('/hospitals/ajaxState', 'HospitalController@ajaxState');
/*
|--------------------------------------------------------------------------
| Hospital custom find
|--------------------------------------------------------------------------
*/
Route::get('/hospital-city/{id}/{expertiseSearch?}', 'HomeController@hospitalCity')->where('id', '[0-9]+');
/*
|--------------------------------------------------------------------------
| Clinic custom find
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| State find  => Index - Section 1
|--------------------------------------------------------------------------
*/
Route::post('/search/bundle/ajaxStateList', 'HomeController@bundleAjaxStateList');
Route::post('/search/bundle/ajaxCityListForExpertise', 'HomeController@bundleAjaxCityListForExpertise');
Route::post('/search/bundle/ajaxHospitalListForExpertise', 'HomeController@bundleAjaxHospitalListForExpertise');


/*
|--------------------------------------------------------------------------
| Hospital find
|--------------------------------------------------------------------------
*/
Route::get('/hospital/{id}/{name}', 'HospitalController@find')->where('id', '[0-9]+');
Route::get('/hospital-doctorIntended/{doctorId}/{doctorLastName}', 'HospitalController@findDoctorTime')->where('id', '[0-9]+');
Route::post('/hospital/expertiseSearchCustomize', 'HospitalController@expertiseSearchCustomize');
Route::get('/hospitalDetail/{id}/{name}', 'HospitalController@detail')->where('id', '[0-9]+');
Route::post('/search/bundle/ajaxDoctorList', 'HospitalController@bundleAjaxDoctorList');
Route::post('/search/bundle/ajaxDoctorGalleryList', 'HospitalController@bundleAjaxGalleryList');
Route::post('/search/bundle/ajaxDoctorAbout', 'HospitalController@bundleAjaxAbout');
Route::post('/search/bundle/ajaxHospitalTime', 'HospitalController@ajaxHospitalTime');
Route::post('/search/bundle/ajaxHospitalTime-Doctor', 'HospitalController@ajaxHospitalTimeDoctor');

Route::post('/search/bundle/doctorAjaxDoctorList', 'HospitalController@doctorBundleAjaxDoctorList');
Route::post('/search/bundle/doctorAjaxDoctorAbout', 'HospitalController@doctorBundleAjaxAbout');
Route::post('/search/bundle/doctorAjaxHospitalTime', 'HospitalController@doctorAjaxHospitalTime');
Route::post('/hospital/doctorExpertiseSearchCustomize', 'HospitalController@doctorExpertiseSearchCustomize');

/*---*/
Route::post('/search/bundle/ajaxDoctorListIntended', 'HospitalController@bundleAjaxDoctorListIntended');
Route::post('/search/bundle/ajaxDoctorGalleryListIntended', 'HospitalController@bundleAjaxGalleryListIntended');
Route::post('/search/bundle/ajaxDoctorAboutIntended', 'HospitalController@bundleAjaxAboutIntended');
Route::post('/search/bundle/ajaxHospitalTimeIntended', 'HospitalController@ajaxHospitalTimeIntended');
/*
|--------------------------------------------------------------------------
| Clinic find
|--------------------------------------------------------------------------
*/
Route::get('/clinic/{id}/{name}', 'ClinicController@find')->where('id', '[0-9]+');
//Route::get('clinic/{id}/{name}', 'ClinicController@detail')->where('id', '[0-9]+');

/*
|--------------------------------------------------------------------------
| Doctors search
|--------------------------------------------------------------------------
*/
Route::get('/doctors/{id}/{name}', 'HomeController@doctorsSearch')->where('id', '[0-9]+');
/*
|--------------------------------------------------------------------------
| Expertise section
|--------------------------------------------------------------------------
*/
Route::get('/expertiseCustom/{id}/{name}/{cityId?}', 'HomeController@expertiseSearch')->where('id', '[0-9]+');
Route::get('/expertiseCustom/paginate', 'HomeController@expertiseSearchPaginate');

/*
|--------------------------------------------------------------------------
| Hospital detail ajax change section => Section 2
|--------------------------------------------------------------------------
*/
Route::post('/search/bundle/ajaxCityList', 'HomeController@bundleAjaxCityList');
Route::post('/search/bundle/ajaxExpertiseList', 'HomeController@bundleAjaxExpertiseList');

//Route::get('/search/{healthcareCenter}/{id}', 'HomeController@search')->where('id', '[0-9]+');
//Route::get('/healthcareCenterList/{id}/{healthcareCenterList}', 'HomeController@hlist')->where('id', '[0-9]+');


/*
|--------------------------------------------------------------------------
| Default home page
|--------------------------------------------------------------------------
*/
Route::get('/', 'HomeController@index');







/*
|--------------------------------------------------------------------------
| Admin panel routing
| Middleware auth
|--------------------------------------------------------------------------
*/
Route::resource('/manager', 'ManagerController', ['except' => ['create', 'store']]);
Route::resource('/city', 'CityController');
Route::resource('/state', 'StateController');
Route::resource('/expertise', 'ExpertiseController');
Route::resource('/doctor', 'DoctorController');
Route::post('/doctor/ajaxCityList', 'DoctorController@ajaxCityList');
Route::post('/doctor/search', 'DoctorController@ajaxSearch');
Route::resource('/healthcareCenter', 'HealthcareCenterController');
Route::resource('/healthcareCenterList', 'HealthcareCenterListController');
Route::get('/healthcareCenterList/{id}/img', 'HealthcareCenterListController@img');
Route::post('/healthcareCenterList/img', 'HealthcareCenterListController@upload');
Route::post('/healthcareCenterList/ajaxCityList', 'HealthcareCenterListController@ajaxCityList');
Route::resource('/timing', 'TimingController');
Route::resource('/dashboard', 'DashboardController');
Route::post('/dashboard/confirmDoctor', 'DashboardController@confirmDoctor');
Route::post('/dashboard/doctorDetail', 'DashboardController@doctorDetail');
Route::post('/dashboard/confirmTime', 'DashboardController@confirmTime');
Route::post('/dashboard/timeDetail', 'DashboardController@timeDetail');

//Route::resource('/gallery', 'GalleryController');



/*
|--------------------------------------------------------------------------
| Role section
| Middleware auth
|--------------------------------------------------------------------------
*/
// Hospital Queue
Route::resource('/hospitalQueue', 'HospitalQueueController', ['only' => 'index']);
Route::post('/hospitalQueue/searchAll', 'HospitalQueueController@searchAll');
Route::post('/hospitalQueue/cancel', 'HospitalQueueController@cancel');
Route::post('/hospitalQueue/cancelTime', 'HospitalQueueController@cancelTime');
Route::get('/hospitalQueue/text/{time}', 'HospitalQueueController@textFileGenerate')->where('time', '[0-9]+');
Route::post('/hospitalQueue/returnAmount', 'HospitalQueueController@returnAmount');
Route::post('/hospitalQueue/activeVisits', 'HospitalQueueController@activeVisits');
Route::post('/hospitalQueue/showVisits', 'HospitalQueueController@showVisits');
Route::post('/hospitalQueue/history', 'HospitalQueueController@history');
// Doctor Queue
Route::resource('/policlinicQueue', 'PoliclinicQueueController', ['only' => 'index']);
Route::post('/policlinicQueue/searchAll', 'PoliclinicQueueController@searchAll');
Route::post('/policlinicQueue/cancel', 'PoliclinicQueueController@cancel');
Route::post('/policlinicQueue/cancelTime', 'PoliclinicQueueController@cancelTime');
Route::get('/policlinicQueue/text/{time}', 'PoliclinicQueueController@textFileGenerate')->where('time', '[0-9]+');
Route::post('/policlinicQueue/returnAmount', 'PoliclinicQueueController@returnAmount');
Route::post('/policlinicQueue/activeVisits', 'PoliclinicQueueController@activeVisits');
Route::post('/policlinicQueue/showVisits', 'PoliclinicQueueController@showVisits');
// Role Doctor
Route::resource('/roleDoctor', 'RoleDoctorController');
Route::get('/roleDoctor/{id}/time', 'RoleDoctorController@time')->where('id', '[0-9]+');
Route::post('/roleDoctor/addTime', 'RoleDoctorController@addTime');
Route::get('/roleDoctor/{id}/ticket', 'RoleDoctorController@ticket')->where('id', '[0-9]+');
Route::post('/roleDoctor/addTicket', 'RoleDoctorController@addTicket');
Route::post('/roleDoctor/readTicket', 'RoleDoctorController@readTicket');
Route::get('/roleDoctor/{id}/clinic', 'RoleDoctorController@clinic')->where('id', '[0-9]+');
Route::post('/roleDoctor/clinic', 'RoleDoctorController@updateClinic');
Route::get('/roleDoctor/{id}/changePassword', 'RoleDoctorController@changePassword')->where('id', '[0-9]+');
Route::post('/roleDoctor/changePassword', 'RoleDoctorController@changePasswordAction');
Route::get('/roleDoctor/{id}/setting', 'RoleDoctorController@setting')->where('id', '[0-9]+');
Route::post('/roleDoctor/{id}/settingUpdate', 'RoleDoctorController@settingUpdate');
// Role Hospital
Route::resource('/roleHospital', 'RoleHospitalController');
Route::get('/roleHospital/{id}/addDoctor', 'RoleHospitalController@doctor')->where('id', '[0-9]+');
Route::post('/roleHospital/addDoctor', 'RoleHospitalController@addDoctor');
Route::get('/roleHospital/{id}/time', 'RoleHospitalController@time')->where('id', '[0-9]+');
Route::post('/roleHospital/addTime', 'RoleHospitalController@addTime');
Route::get('/roleHospital/{id}/editTime', 'RoleHospitalController@editTime')->where('id', '[0-9]+');
Route::post('/roleHospital/updateTime', 'RoleHospitalController@updateTime');
Route::get('/roleHospital/{id}/showTime', 'RoleHospitalController@showTime')->where('id', '[0-9]+');
Route::post('/roleHospital/destroyTime', 'RoleHospitalController@destroyTime');
Route::get('/roleHospital/{id}/sendTicket', 'RoleHospitalController@ticket')->where('id', '[0-9]+');
Route::post('/roleHospital/sendTicket', 'RoleHospitalController@sendTicket');
Route::post('/roleHospital/readTicket', 'RoleHospitalController@readTicket');
Route::get('/roleHospital/{id}/changePassword', 'RoleHospitalController@changePassword')->where('id', '[0-9]+');
Route::post('/roleHospital/changePassword', 'RoleHospitalController@changePasswordAction');
Route::get('/roleHospital/{id}/addUser', 'RoleHospitalController@addUser')->where('id', '[0-9]+');
Route::post('/roleHospital/addUser', 'RoleHospitalController@addUserAction');
Route::get('/roleHospital/{id}/setting', 'RoleHospitalController@setting')->where('id', '[0-9]+');
Route::post('/roleHospital/{id}/settingUpdate', 'RoleHospitalController@settingUpdate');
Route::get('/roleHospital/{id}/pic', 'RoleHospitalController@pic')->where('id', '[0-9]+');
Route::post('/roleHospital/picUpload', 'RoleHospitalController@picUpload');
Route::get('/roleHospital/{id}/picDelete', 'RoleHospitalController@picDelete')->where('id', '[0-9]+');
Route::get('/sms/txt', 'SmsController@txt');
Route::get('/sms/{id}/editTxt', 'SmsController@editTxt')->where('id', '[0-9]+');
Route::post('/sms/editTxtSend', 'SmsController@editTxtSend');
Route::get('/sms/setting', 'SmsController@setting');
Route::get('/sms/{id}/editSetting', 'SmsController@editSetting')->where('id', '[0-9]+');
Route::post('/sms/editSettingSend', 'SmsController@editSettingSend');
Route::get('/sms/settingAdd', 'SmsController@settingAdd');
Route::post('/sms/settingAddSend', 'SmsController@settingAddSend');
Route::get('/sms/list', 'SmsController@smsList');
    // Statistics
Route::get('/statistics', 'StatisticsController@index');
Route::post('/statistics/ajaxResult', 'StatisticsController@ajaxResult');
Route::post('/statistics/cancelResult', 'StatisticsController@cancelResult');
Route::get('/statistics/chart', 'StatisticsController@chart');
Route::post('/statistics/chartResult', 'StatisticsController@ajaxChart');
Route::get('/statistics/excelDownload/{start}/{end}', 'StatisticsController@excelDownload');

// Dashboard Admin
Route::resource('/dashboardAdmin', 'DashboardAdminController', ['only' => 'index']);
Route::get('/dashboardAdmin/{id}/answer', 'DashboardAdminController@answer')->where('id', '[0-9]+');
Route::post('/dashboardAdmin/sendAnswer', 'DashboardAdminController@sendAnswer');
Route::get('/dashboardAdmin/{id}/doctorTicketAnswer', 'DashboardAdminController@doctorTicketAnswer')->where('id', '[0-9]+');
Route::post('/dashboardAdmin/doctorTicketSendAnswer', 'DashboardAdminController@doctorTicketSendAnswer');
// Role Hospital Time
Route::resource('/roleHospitalTime', 'roleHospitalTimeController', ['only' => 'index']);
Route::post('/roleHospitalTime/ajaxExpertise', 'roleHospitalTimeController@ajaxExpertise');