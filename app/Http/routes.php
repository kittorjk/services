<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'root', function () {
    $user = Session::get('user');
    $service = Session::get('service');
    return view('app.index', ['user' => $user, 'service' => $service]);
}]);

Route::get('/getuseragent', [function(){
    return request();
}]);

Route::get('/adm', 'AdminController@menu')->name('admin_menu');

Route::get('/login', function(){
    return redirect()->route('root');
});
Route::post('login', 'LoginController@login');
Route::get('logout/{service}', 'LoginController@logout');
Route::get('/login/pw_recovery', 'LoginController@pw_recovery_form');
Route::post('/login/pw_recovery', 'LoginController@pw_recover');
Route::get('/client_session', 'LoginController@index');

Route::resource('/active', 'ActiveController');
Route::resource('/branch', 'BranchController');
Route::resource('/cite', 'CitesController');
Route::resource('/client_listed_material', 'ClientListedMaterialController');
Route::resource('/contact', 'ContactController');
Route::resource('/employee', 'EmployeeController');
Route::resource('/item', 'ItemController');
Route::resource('/license', 'LicenseController');
Route::resource('/rbs_site_characteristics', 'RbsSiteCharacteristicController');
Route::resource('/service_parameter', 'ServiceParameterController');
Route::resource('/staff', 'StaffController');
Route::resource('/task_category', 'TaskSummaryCategoryController');
Route::resource('/tech_group', 'TechGroupController');
Route::resource('/user', 'UserController');

Route::get('/activity', 'ActivityController@index');
Route::get('/activity/{id}', 'ActivityController@activities_per_task');
Route::get('/activity/{id}/edit', 'ActivityController@edit');
Route::put('/activity/{id}', 'ActivityController@update');
//Route::get('/activity/{id}/show', 'ActivityController@show');
Route::get('/activity/{id}/create', 'ActivityController@create');
Route::post('/activity', 'ActivityController@store');
Route::delete('/activity/{id}', 'ActivityController@destroy');

/* Ajax Controller */
//Route::get('/ajax', 'AjaxController@verify');
Route::post('/check_existence', 'AjaxController@check_existence');
Route::post('/check_email_address', 'AjaxController@check_email_address');
Route::post('/check_material_existence', 'AjaxController@check_material_existence');
Route::post('/dynamic_sites', 'AjaxController@dynamic_sites');
Route::post('/dynamic_actives', 'AjaxController@dynamic_actives');
Route::post('/dynamic_items', 'AjaxController@dynamic_items');
Route::post('/dynamic_guaranteeable', 'AjaxController@dynamic_guaranteeable');
Route::post('/dynamic_assignment/sub_type', 'AjaxController@dynamic_assignment_sub_type');
Route::post('/dynamic_requirement/{active}', 'AjaxController@dynamic_requirement');
Route::post('/load_oc_amount_values', 'AjaxController@load_oc_amount_values');
Route::post('/load_oc_values', 'AjaxController@load_oc_values');
Route::post('/load_task_values', 'AjaxController@load_task_values');
Route::post('/load_activity_info', 'AjaxController@load_activity_info');
Route::post('/load_event_info', 'AjaxController@load_event_info');
Route::post('/load_material_available', 'AjaxController@load_material_available');
Route::post('/load_name/{form}', 'AjaxController@load_name');
Route::post('/autocomplete/{table}', 'AjaxController@autocomplete');
Route::post('/flag/{table}', 'AjaxController@flag_change');
Route::post('/retrieve/{column}', 'AjaxController@retrieve_column');
Route::post('/status_update/{option}', 'AjaxController@status_update');
Route::post('/set_current_url', 'AjaxController@set_current_url');
Route::post('/set_item_unit_cost', 'AjaxController@set_item_unit_cost');
Route::post('/set_oc_executed_amount', 'AjaxController@set_oc_executed_amount');

Route::get('/assignment/expense_report/{type}', 'AssignmentController@expense_report_form');
Route::post('/assignment/expense_report/{type}', 'AssignmentController@expense_report');
Route::get('/assignment/generate/{type}', 'AssignmentController@generate_from_model');
Route::get('/assignment/mail/{type}/select-recipient', 'AssignmentController@select_mail_recipient');
Route::post('/assignment/mail/{type}', 'AssignmentController@send_selected_mail');
Route::get('/assignment/progress/items/{id}', 'AssignmentController@items_general_progress');
Route::get('/assignment/progress/per_site/{id}', 'AssignmentController@per_site_general_progress');
Route::get('/assignment/refresh_data/{id}', 'AssignmentController@refresh_data');
Route::get('/assignment/stat/{id}', 'AssignmentController@modify_status');
Route::resource('/assignment', 'AssignmentController');
//Route::get('project_fnc/{id}', 'AssignmentController@show_financial_details');

Route::get('bill_upstat/{id}', 'BillController@update_status');
Route::resource('/bill', 'BillController');

Route::get('/calibration/close/{id}', 'CalibrationController@close_record');
Route::resource('/calibration', 'CalibrationController');

Route::get('/characteristics/device/', 'DeviceCharacteristicController@index');
Route::get('/characteristics/device/{id}', 'DeviceCharacteristicController@device_characteristics');
Route::get('/characteristics/device/{id}/edit', 'DeviceCharacteristicController@edit');
Route::put('/characteristics/device/{id}', 'DeviceCharacteristicController@update');
//Route::get('/characteristics/device/{id}/show', 'DeviceCharacteristicController@show');
Route::get('/characteristics/device/{id}/create', 'DeviceCharacteristicController@create');
Route::post('/characteristics/device', 'DeviceCharacteristicController@store');
Route::delete('/characteristics/device/{id}', 'DeviceCharacteristicController@destroy');

Route::get('/corporate_line/disable', 'CorpLineController@disable_form');
Route::put('/corporate_line/disable', 'CorpLineController@disable_record');
Route::resource('/corporate_line', 'CorpLineController');

Route::get('/dead_interval/close/{id}', 'DeadIntervalController@close_interval');
Route::resource('/dead_interval', 'DeadIntervalController');

Route::get('/device/change/main_pic_id/{id}', 'DeviceController@main_pic_id_form');
Route::post('/device/change/main_pic_id/{id}', 'DeviceController@change_main_pic_id');
Route::get('/device/disable', 'DeviceController@disable_form');
Route::put('/device/disable', 'DeviceController@disable_record');
Route::get('/device/report_malfunction/{id}', 'DeviceController@report_malfunction_form');
Route::put('/device/report_malfunction/{id}', 'DeviceController@record_malfunction_report');
Route::resource('/device', 'DeviceController');

Route::get('device_failure_report/move_stat', 'DvcFailureReportController@move_stat');
Route::resource('/device_failure_report', 'DvcFailureReportController');

Route::get('/device_requirement/reject/{id}', 'DeviceRequirementController@reject_form');
Route::put('/device_requirement/reject/{id}', 'DeviceRequirementController@reject');
Route::resource('/device_requirement', 'DeviceRequirementController');

Route::get('/driver/confirm/{id}', 'DriverController@reception_confirmation_form');
Route::put('/driver/confirm/{id}', 'DriverController@confirm_reception');
//Route::get('/driver/devolution/{id}', 'DriverController@devolution_form'); //deprecated
//Route::post('/driver/devolution', 'DriverController@record_devolution'); //deprecated
Route::resource('/driver', 'DriverController');

Route::get('/event', 'EventController@index');
Route::get('/event/{type}/{id}', 'EventController@events_per_type');
Route::get('/event/{type}/{id}/edit', 'EventController@edit');
Route::put('/event/{type}/{id}', 'EventController@update');
Route::get('/event/{type}/{id}/create', 'EventController@create');
Route::post('/event/{type}/{id}', 'EventController@store');
Route::delete('/event/{type}/{id}', 'EventController@destroy');
//Route::get('/event/{type}/{id}/show', 'EventController@show');
//Route::get('/event/details/{id}', 'EventController@show_event_info');
//Route::get('/event/{id}', 'EventController@site_selector');

Route::get('excel/info/{type}/{id}', 'ExcelController@export_info_page');
Route::get('excel/load_format/{format}/{id}', 'ExcelController@load_format_file');
Route::post('excel/fill/{format}/{id}', 'ExcelController@fill_uploaded_model');
Route::get('excel/{table}','ExcelController@index');
Route::get('excel/{table}/{id}','ExcelController@summary');
Route::get('excel/report/{type}/{id}', 'ExcelController@report_form');
Route::post('excel/report/{type}/{id}', 'ExcelController@generate_report');
Route::get('import/{type}/{id}', 'ExcelController@import_form');
Route::post('import/{type}/{id}', 'ExcelController@import_items');

Route::get('/exported/files', 'ExportedFilesController@index')->name('exported_files');

Route::get('/files/{type}/{id}', 'FilesController@form_to_upload');
Route::post('/files/{type}/{id}', 'FilesController@uploader');
Route::get('/download/{id}', 'FilesController@download_file');
Route::get('/display_file/{id}', 'FilesController@display');
Route::get('/delete/{type}', 'FilesController@delete_form');
Route::post('/delete/{type}', 'FilesController@delete_file');
Route::resource('/file', 'FilesController');

Route::get('/guarantee/close/{id}', 'GuaranteeController@close_guarantee');
Route::resource('/guarantee', 'GuaranteeController');

Route::get('/history/device/{id}', 'ActiveHistoryController@device_history_records');
Route::get('/history/vehicle/{id}', 'ActiveHistoryController@vehicle_history_records');

//Route::get('/invoice/approve', 'InvoiceController@approve_invoice_form');
//Route::post('/invoice/approve', 'InvoiceController@approve_invoice');
Route::get('/invoice/payment/{id}', 'InvoiceController@payment_form');
Route::put('/invoice/payment/{id}', 'InvoiceController@record_payment');
Route::resource('/invoice', 'InvoiceController');

Route::get('/item_category/stat', 'ItemCategoryController@stat_change');
Route::resource('/item_category', 'ItemCategoryController');

Route::get('join/{model}/{id}', 'AssociationController@join_form');
Route::post('join/{model}/{id}', 'AssociationController@joiner');
Route::get('/detach/{page}/{from_id}/{id}', 'AssociationController@detach');

Route::get('/line_assignation/devolution', 'CorpLineAssignationController@devolution_form');
Route::post('/line_assignation/devolution', 'CorpLineAssignationController@register_devolution');
Route::resource('/line_assignation', 'CorpLineAssignationController');

Route::get('/line_requirement/reject/{id}', 'CorpLineRequirementController@reject_form');
Route::put('/line_requirement/reject/{id}', 'CorpLineRequirementController@reject');
Route::resource('/line_requirement', 'CorpLineRequirementController');

/* For mailing notifications & testing purposes */
Route::get('send-notice/{type}', 'MailController@send_notifications');
Route::get('send-notice/{type}/{id}', 'MailController@send_requested_notification');
//Route::get('sendbasicemail','MailController@basic_email');
Route::get('sendhtmlemail', 'MailController@html_email');
//Route::get('sendattachmentemail','MailController@attachment_email');
Route::get('mail/send/{id}', 'MailController@choose_recipient_form');
Route::post('mail/send/{id}', 'MailController@send_to_new_recipient');
Route::get('mail/resend/{id}', 'MailController@resend_email');
Route::resource('/email', 'MailController');

Route::get('/maintenance/close/{id}', 'MaintenanceController@close_maintenance');
Route::get('/maintenance/request/{type}', 'MaintenanceController@maintenance_required_list');
Route::post('/maintenance/request/{type}', 'MaintenanceController@move_to_maintenance');
Route::resource('/maintenance', 'MaintenanceController');

Route::get('/oc_certificate/print_ack/{code}', 'OcCertificationController@print_ack')->name('oc_certificate_print_ack');
Route::resource('/oc_certificate', 'OcCertificationController');
Route::get('/oc/cancel/{id}', 'OCController@cancel_form');
Route::put('/oc/cancel/{id}', 'OCController@cancel_oc');
Route::get('/oc/reject', 'OCController@reject_form');
Route::put('/oc/reject', 'OCController@reject_oc');
Route::get('approve_oc', 'OCController@approve_form');
Route::post('approve_oc', 'OCController@approve_action');
Route::get('/rejected_ocs', 'OCController@rejected_ocs_list');
Route::resource('/oc', 'OCController');

Route::get('/operator/confirm/{id}', 'OperatorController@reception_confirmation_form');
Route::put('/operator/confirm/{id}', 'OperatorController@confirm_reception');
//Route::get('/operator/devolution/{id}', 'OperatorController@devolution'); //deprecated
Route::resource('/operator', 'OperatorController');

Route::get('order_upstat/{id}', 'OrderController@update_status');
Route::get('recent_qcc', 'OrderController@recent_qcc');
Route::resource('/order', 'OrderController');
//Route::get('/detach_from_order/{type_id}/{order_id}', 'OrderController@detach_from_order');
//Route::get('/order_asoc/{id}', 'OrderController@show_order_associations');

Route::get('/project/cron_end', 'ProjectsController@cron_end');
Route::get('/project/add_assignment/{id}', 'ProjectsController@add_assignment');
//Route::get('/project/applied/{id}', 'ProjectsController@mark_application_done');
Route::get('/project/close/{id}', 'ProjectsController@close_record');
Route::get('/project/expense_report/{type}', 'ProjectsController@expense_report_form');
Route::post('/project/expense_report/{type}', 'ProjectsController@expense_report');
Route::get('/project/generate/{type}', 'ProjectsController@generate_from_model');
Route::resource('/project', 'ProjectsController');
//Route::get('ec_resume/{id}', 'ProjectsController@show_economic_resume');
//Route::get('action/{id}/{flag}', 'ProjectsController@edit_action');

Route::get('/provider/incomplete', 'ProviderController@incomplete_registers');
Route::resource('/provider', 'ProviderController');

Route::get('search/{table}/{id}', 'SearchController@search_form');
Route::get('search_results/{table}/{id}', 'SearchController@search_results');
//Route::post('search/{table}/{id}', 'SearchController@search_results'); Changed to get to work with pagination

Route::get('/site', 'SiteController@index');
Route::get('/site/schedule', 'SiteController@site_schedule');
Route::get('/site/calendar/{id}', 'SiteController@site_items_calendar');
Route::get('/site/clear_all/{id}', 'SiteController@clear_site');
Route::get('/site/expense_report/{type}/{asg_id}', 'SiteController@expense_report_form');
Route::post('/site/expense_report/{type}/{asg_id}', 'SiteController@expense_report');
Route::get('/site/generate/{type}/{asg_id}', 'SiteController@generate_from_model');
Route::get('/site/refresh_data/{id}', 'SiteController@refresh_data');
Route::get('/site/stat/{id}', 'SiteController@modify_status');
Route::get('/site/set_global_dates/{id}', 'SiteController@set_global_dates_form');
Route::put('/site/set_global_dates/{id}', 'SiteController@set_global_dates');
Route::get('/site/{id}', 'SiteController@sites_per_project');
Route::get('/site/{id}/control', 'SiteController@control_form');
Route::put('/site/{id}/control', 'siteController@config_control');
Route::get('/site/{id}/edit', 'SiteController@edit');
Route::put('/site/{id}', 'SiteController@update');
Route::get('/site/{id}/show', 'SiteController@show');
Route::get('/site/{id}/create', 'SiteController@create');
Route::post('/site', 'SiteController@store');
Route::delete('site/{id}', 'SiteController@destroy');
//Route::get('site_fnc/{id}', 'SiteController@show_financial_details');

Route::get('/staff_role/{id}/enable', 'StaffRoleController@enable_role');
Route::resource('/staff_role', 'StaffRoleController');

Route::get('/stipend_request/approve_list', 'StipendRequestController@pending_approval_list');
Route::get('/stipend_request/close', 'StipendRequestController@close_request');
Route::get('/stipend_request/observed_list', 'StipendRequestController@observed_list');
Route::get('/stipend_request/payment_list', 'StipendRequestController@pending_payment_list');
//Route::get('/stipend_request/approve', 'StipendRequestController@approve_request');
Route::get('/stipend_request/request_adm', 'StipendRequestController@request_adm');
Route::get('/stipend_request/stat', 'StipendRequestController@change_status_form');
Route::post('/stipend_request/stat', 'StipendRequestController@change_status');
Route::resource('/stipend_request', 'StipendRequestController');

Route::get('/task', 'TaskController@index');
Route::get('/task/clear_all/{id}', 'TaskController@clear_task');
Route::get('/task/refresh_data/{id}', 'TaskController@refresh_data');
Route::get('/task/stat/{id}', 'TaskController@modify_status');
Route::get('/task/{id}', 'TaskController@tasks_per_site');
Route::get('/task/{id}/edit', 'TaskController@edit');
Route::put('/task/{id}', 'TaskController@update');
Route::get('/task/{id}/show', 'TaskController@show');
Route::get('/task/{id}/add', 'TaskController@list_items');
Route::post('/task/{id}/add', 'TaskController@add_from_list');
Route::get('/task/{id}/create', 'TaskController@create');
Route::post('/task', 'TaskController@store');
Route::delete('/task/{id}', 'TaskController@destroy');
//Route::get('task_fnc/{id}', 'TaskController@show_financial_details');

Route::get('/tender/add_contract/{id}', 'TenderController@add_contract_form');
Route::post('/tender/add_contract/{id}', 'TenderController@add_contract');
Route::get('/tender/applied/{id}', 'TenderController@chg_stat_sent');
Route::get('/tender/close/{id}', 'TenderController@chg_stat_close');
Route::get('/tender/won/{id}', 'TenderController@chg_stat_won');
Route::resource('/tender', 'TenderController');

Route::get('/vehicle/change/main_pic_id/{id}', 'VehicleController@main_pic_id_form');
Route::post('/vehicle/change/main_pic_id/{id}', 'VehicleController@change_main_pic_id');
Route::get('/vehicle/disable', 'VehicleController@disable_form');
Route::put('/vehicle/disable', 'VehicleController@disable_record');
Route::get('/vehicle/report_malfunction/{id}', 'VehicleController@report_malfunction_form');
Route::put('/vehicle/report_malfunction/{id}', 'VehicleController@record_malfunction_report');
Route::get('/vehicle/link/{type}/{id}', 'VehicleController@link_model_form');
Route::put('/vehicle/link/{type}/{id}', 'VehicleController@record_linked_model');
Route::resource('/vehicle', 'VehicleController');

Route::get('/vehicle_condition', 'VehicleConditionController@index');
Route::get('/vehicle_condition/{id}', 'VehicleConditionController@vehicle_records');
Route::get('/vehicle_condition/{id}/edit', 'VehicleConditionController@edit');
Route::put('/vehicle_condition/{id}', 'VehicleConditionController@update');
Route::get('/vehicle_condition/{id}/show', 'VehicleConditionController@show');
Route::get('/vehicle_condition/{id}/create', 'VehicleConditionController@create');
Route::post('/vehicle_condition', 'VehicleConditionController@store');
Route::delete('/vehicle_condition/{id}', 'VehicleConditionController@destroy');

Route::get('vehicle_failure_report/move_stat', 'VhcFailureReportController@move_stat');
Route::resource('/vehicle_failure_report', 'VhcFailureReportController');

Route::get('/vehicle_requirement/reject/{id}', 'VehicleRequirementController@reject_form');
Route::put('/vehicle_requirement/reject/{id}', 'VehicleRequirementController@reject');
Route::resource('/vehicle_requirement', 'VehicleRequirementController');

//Routes for Warehouse module (no longer in use)
/*
Route::get('/material/change/main_pic_id/{id}', 'MaterialController@main_pic_id_form');
Route::post('/material/change/main_pic_id/{id}', 'MaterialController@change_main_pic_id');
Route::resource('/material', 'MaterialController');

Route::get('/warehouse/events/{id}', 'WarehouseController@warehouse_events');
Route::get('/warehouse/materials/{id}', 'WarehouseController@materials_per_warehouse');
Route::get('/warehouse/transfer', 'WarehouseController@transfer_form');
Route::post('/warehouse/transfer', 'WarehouseController@transfer_materials');
Route::resource('/warehouse', 'WarehouseController');
Route::resource('/wh_entry', 'WarehouseEntryController');
Route::resource('/wh_outlet', 'WarehouseOutletController');
*/

//Contract merged with Project
//Route::get('/contract/close/{id}', 'ContractController@close_contract');
//Route::resource('/contract', 'ContractController');

//Rbs_viatics replaced by stipend_requests
/*
Route::get('/rbs_viatic/approve_list', 'RbsViaticController@pending_approval_list');
Route::get('/rbs_viatic/observed_list', 'RbsViaticController@observed_list');
Route::get('/rbs_viatic/approve/{id}', 'RbsViaticController@approve');
Route::get('/rbs_viatic/status/{id}', 'RbsViaticController@change_status_form');
Route::post('/rbs_viatic/status/{id}', 'RbsViaticController@change_status');
Route::resource('/rbs_viatic', 'RbsViaticController');
*/

/*
Route::get('/ajax', function(){
    if(Request::ajax()){
        return 'Hola';
    }
});

Route::get('install', function() {
    try {
        Artisan::call('migrate');
        echo 'Migracion completa';
    } catch (Exception $e) {
        response()->make($e->getMessage(), 500);
    }
});

Route::get('seed', function(){
    try{
        Artisan::call('db:seed', ['--class' => 'CiteSeeder']);
        echo "Completado";
    }
    catch (Exception $e){
        response()->make($e->getMessage(), 500);
    }
});
*/

/*
route::get('cat_generate', function(){
    $categories = App\Item::select('category', 'area')->where('category', '<>', '')->groupBy('category')->get();

    $count = 0;

    foreach($categories as $category){
        if(!App\ItemCategory::where('name', $category->category)->exists()){

            if (strpos($category->category, 'Adicional') === false) {
                $new_category = new App\ItemCategory();
                $new_category->name = $category->category;
                $new_category->area = $category->area;
                $new_category->status = 1;

                $new_category->save();

                $count++;

                $items = App\Item::where('category', $new_category->name)->get();

                foreach($items as $item){
                    $item->item_category_id = $new_category->id;

                    $item->save();
                }
            }
        }
    }

    return $count==1 ? 'Se ha creado 1 categoría' : 'Se han creado '.$count.' categorías';
});
*/

/*
route::get('generate_actions', function(){
    $gen_users = App\User::select('id','status')->get();

    $count = 0;

    foreach($gen_users as $user){
        $data = $user->status=='Activo' ? 1 : 0;

        $action = new App\UserAction();
        $action->user_id = $user->id;
        $action->enl_ct = $user->priv_level==4 ? 1 : 0;
        $action->enl_oc = $user->priv_level==4 ? 1 : 0;
        $action->enl_prj = $user->priv_level==4 ? 1 : 0;
        $action->enl_acv = $user->priv_level==4 ? 1 : 0;
        $action->enl_adm = $user->priv_level==4 ? 1 : 0;
        $action->ct_upl_fmt = $data;
        $action->ct_vw_all = $user->priv_level==4 ? 1 : 0;
        $action->ct_exp = $data;
        $action->ct_edt = $user->priv_level==4 ? 1 : 0;
        $action->oc_add = $data;
        $action->oc_edt = $data;
        $action->oc_apv_tech = $user->priv_level==4 ? 1 : 0;
        $action->oc_apv_gg = $user->priv_level==4 ? 1 : 0;
        $action->oc_nll = $data;
        $action->oc_exp = $data;
        $action->oc_prv_edt = $data;
        $action->oc_prv_exp = $data;
        $action->oc_ctf_add = $data;
        $action->oc_ctf_del = $user->priv_level==4 ? 1 : 0;
        $action->oc_ctf_edt = $data;
        $action->oc_ctf_exp = $data;
        $action->oc_inv_edt = $data;
        $action->oc_inv_exp = $user->priv_level==4 ? 1 : 0;
        $action->oc_inv_pmt = $user->priv_level==4 ? 1 : 0;
        $action->prj_edt = $data;
        $action->prj_exp = $data;
        $action->prj_vw_eco = $user->priv_level==4 ? 1 : 0;
        $action->prj_acc_rdr = $user->priv_level==4 ? 1 : 0;
        $action->prj_acc_wty = $user->priv_level==4 ? 1 : 0;
        $action->prj_bill_exp = $data;
        $action->prj_asg_edt = $data;
        $action->prj_asg_exp = $data;
        $action->prj_evt_edt = $data;
        $action->prj_di_edt = $data;
        $action->prj_ctc_edt = $data;
        $action->prj_st_edt = $data;
        $action->prj_st_clr = $user->priv_level==4 ? 1 : 0;
        $action->prj_st_del = $user->priv_level==4 ? 1 : 0;
        $action->prj_st_exp = $data;
        $action->prj_vtc_mod = $user->priv_level==4 ? 1 : 0;
        $action->prj_vtc_edt = $data;
        $action->prj_vtc_pmt = $user->priv_level==4 ? 1 : 0;
        $action->prj_vtc_exp = $data;
        $action->prj_vtc_rep = $user->priv_level==4 ? 1 : 0;
        $action->prj_tk_edt = $data;
        $action->prj_tk_clr = $user->priv_level==4 ? 1 : 0;
        $action->prj_tk_del = $user->priv_level==4 ? 1 : 0;
        $action->prj_tk_exp = $data;
        $action->prj_acc_cat = $data;
        $action->prj_cat_exp = $data;
        $action->prj_act_edt = $data;
        $action->prj_act_del = $user->priv_level==4 ? 1 : 0;
        $action->prj_act_exp = $data;
        $action->acv_vhc_req = $data;
        $action->acv_vhc_edt = $user->priv_level==4 ? 1 : 0;
        $action->acv_vhc_add = $user->priv_level==4 ? 1 : 0;
        $action->acv_vhc_exp = $data;
        $action->acv_vfr_add = $data;
        $action->acv_vfr_mod = $data;
        $action->acv_drv_upl_fmt = $data;
        $action->acv_vhc_lic_mod = $data;
        $action->acv_dvc_req = $data;
        $action->acv_dvc_edt = $data;
        $action->acv_dvc_add = $user->priv_level==4 ? 1 : 0;
        $action->acv_dvc_exp = $data;
        $action->acv_dfr_add = $data;
        $action->acv_dfr_mod = $data;
        $action->acv_cbr_mod = $user->priv_level==4 ? 1 : 0;
        $action->acv_cbr_exp = $user->priv_level==4 ? 1 : 0;
        $action->acv_mnt_add = $data;
        $action->acv_mnt_edt = $data;
        $action->acv_mnt_exp = $data;
        $action->acv_ln_req = $data;
        $action->acv_ln_edt = $user->priv_level==4 ? 1 : 0;
        $action->acv_ln_add = $user->priv_level==4 ? 1 : 0;
        $action->acv_ln_asg = $data;
        $action->acv_ln_exp = $data;
        $action->acc_adm = $user->priv_level==4 ? 1 : 0;
        $action->adm_add_usr = $user->priv_level==4 ? 1 : 0;
        $action->adm_acc_file = $user->priv_level==4 ? 1 : 0;
        $action->adm_file_del = $user->priv_level==4 ? 1 : 0;
        $action->adm_file_exp = $user->priv_level==4 ? 1 : 0;
        $action->adm_acc_mail = $user->priv_level==4 ? 1 : 0;
        $action->adm_acc_stf = $user->priv_level==4 ? 1 : 0;
        $action->adm_acc_bch = $user->priv_level==4 ? 1 : 0;
        $action->adm_bch_mod = $user->priv_level==4 ? 1 : 0;

        $action->save();

        $count++;
    }

    return $count==1 ? 'Se ha insertado 1 registro' : 'Se han insertado '.$count.' registros';
});
*/
/*
route::get('link_user_employee', function(){
    $employees = App\Employee::all();
    $users = App\User::all();

    $count = 0;

    foreach($employees as $employee){
        foreach($users as $user){
            if($user->full_name==$employee->first_name.' '.$employee->last_name){
                $employee->access_id = $user->id;
                $employee->save();

                $count++;
            }
        }
    }

    return $count==1 ? 'Se ha enlazado 1 registro' : 'Se han enlazado '.$count.' registros';
});
*/