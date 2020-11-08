<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Admin_api';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'Admin_api/login';
$route['get-state'] = 'Admin_api/get_state';
$route['get-cities-by-state'] = 'Admin_api/get_cities_by_state';
// $route['get-designation'] = 'Admin_api/get_designation';


// *******************************   Society Master*************************************************************
$route['add-society'] = 'Admin_api/add_company';
$route['get-all-soc-master'] = 'Admin_api/get_all_soc_master';
$route['get-company'] = 'Admin_api/get_company';
$route['update-soc'] = 'Admin_api/update_society';
$route['delete-society'] = 'Admin_api/delete_society';

// *************************************** Chairmen Master ****************************************************

$route['add-chairmen'] = 'Admin_api/add_chairmen';
$route['get-all-chairmen'] = 'Admin_api/get_all_chairmen';
$route['get-chairmen'] = 'Admin_api/get_chairmen';
$route['update-chairmen'] = 'Admin_api/update_chairmen';
$route['delete-chairmen'] = 'Admin_api/delete_chairmen';


// ************************************** Secretary Master ***************************************************

$route['add-secretary'] = 'Admin_api/add_secretary';
$route['get-all-secretary'] = 'Admin_api/get_all_secretary';
$route['get-secretary'] = 'Admin_api/get_secretary';
$route['update-secretary'] = 'Admin_api/update_secretary';
$route['delete-secretary'] = 'Admin_api/delete_secretary';

// ************************************** Treasurer Master ***************************************************

$route['add-treasurer'] = 'Admin_api/add_treasurer';
$route['get-all-treasurer'] = 'Admin_api/get_all_treasurer';
$route['get-treasurer'] = 'Admin_api/get_treasurer';
$route['update-treasurer'] = 'Admin_api/update_treasurer';
$route['delete-treasurer'] = 'Admin_api/delete_treasurer';


// ******************************************* Manager ****************************************************

$route['add-manager'] = 'Admin_api/add_manager';
$route['get-all-manager'] = 'Admin_api/get_all_manager';
$route['get-manager'] = 'Admin_api/get_manager';
$route['update-manager'] = 'Admin_api/update_manager';
$route['delete-manager'] = 'Admin_api/delete_manager';

// *******************************************Owner Master ******************************************************

$route['add-owner'] = 'Admin_api/add_owner';
$route['get-all-owner'] = 'Admin_api/get_all_owner';
$route['get-owner'] = 'Admin_api/get_owner';
$route['update-owner'] = 'Admin_api/update_owner';
$route['delete-owner'] = 'Admin_api/delete_owner';

// ************************************** Rental Master ***********************************************************

$route['add-rental'] = 'Admin_api/add_rental';
$route['get-all-rental'] = 'Admin_api/get_all_rental';
$route['get-rental'] = 'Admin_api/get_rental';
$route['update-rental'] = 'Admin_api/update_rental';
$route['delete-rental'] = 'Admin_api/delete_rental';

// ************************************** Vehicle Master ***********************************************************

$route['add-vehicle'] = 'Admin_api/add_vehicle';
$route['get-all-vehicle'] = 'Admin_api/get_all_vehicle';
$route['get-vehicle'] = 'Admin_api/get_vehicle';
$route['update-vehicle'] = 'Admin_api/update_vehicle';
$route['delete-vehicle'] = 'Admin_api/delete_vehicle';


// ************************************** Vendor Master ***********************************************************

$route['add-vendor'] = 'Admin_api/add_vendor';
$route['get-all-vendor'] = 'Admin_api/get_all_vendor';
$route['get-vendor'] = 'Admin_api/get_vendor';
$route['update-vendor'] = 'Admin_api/update_vendor';
$route['delete-vendor'] = 'Admin_api/delete_vendor';



// ************************************** Security Master ***********************************************************

$route['add-security'] = 'Admin_api/add_security';
$route['get-all-security'] = 'Admin_api/get_all_security';
$route['get-security'] = 'Admin_api/get_security';
$route['update-security'] = 'Admin_api/update_security';
$route['delete-security'] = 'Admin_api/delete_security';

// ************************************** Security Master ***********************************************************

$route['add-emergency'] = 'Admin_api/add_emergency';
$route['get-all-emergency'] = 'Admin_api/get_all_emergency';
$route['get-emergency'] = 'Admin_api/get_emergency';
$route['update-emergency'] = 'Admin_api/update_emergency';
$route['delete-emergency'] = 'Admin_api/delete_emergency';

// ************************************** Designation Master********************************************************
$route['get-designations'] = 'Admin_api/get_designations';
$route['add-designations'] = 'Admin_api/add_designations';

// ********************************************* Change Password************************************************

$route['change-password'] = 'Admin_api/change_password';



// *************************************** Setting Page ***********************************************************

$route['add-setting'] = 'Admin_api/add_setting';
$route['get-all-setting-page'] = 'Admin_api/get_all_setting_page';
$route['get-setting-page'] = 'Admin_api/get_setting_page';
$route['update-setting-page'] = 'Admin_api/update_setting_page';
$route['delete-setting-page'] = 'Admin_api/delete_setting_page';
// ************************************** Invoice Series *********************************
$route['get-transaction-type'] = 'Admin_api/get_transaction_type';
$route['add-invoice-series'] = 'Admin_api/add_invoice_series';
$route['get-invoice-series'] = 'Admin_api/get_invoice_series';
$route['get-invoice-series-on-id'] = 'Admin_api/get_invoice_series_on_id';
$route['update-invoice-series'] = 'Admin_api/update_invoice_series';
$route['delete-invoice-series'] = 'Admin_api/delete_invoice_series';
$route['get-invoice-no'] = 'Admin_api/get_invoice_no';
$route['get-invoice-data'] = 'Admin_api/get_invoice_data';
$route['add-invoice-data'] = 'Admin_api/add_invoice_data';
$route['get-invoice-on-id'] = 'Admin_api/get_invoice_on_id';
$route['update-invoice-data'] = 'Admin_api/update_invoice_data';
$route['delete-invoice'] = 'Admin_api/delete_invoice';
$route['get-invoice-pdf'] = 'Admin_api/get_invoice_pdf';
$route['get-data-on-type'] = 'Admin_api/get_data_on_type';
$route['get-data-on-customer-id'] = 'Admin_api/get_data_on_customer_id';
$route['total-society-count'] = 'Admin_api/total_society_count';
$route['total-admin-count'] = 'Admin_api/total_admin_count';
$route['add-expenses-data'] = 'Admin_api/add_expenses_data';
$route['get-expenses-data'] = 'Admin_api/get_expenses_data';
$route['get-expenses-on-id'] = 'Admin_api/get_expenses_on_id';
$route['update-expenses-data'] = 'Admin_api/update_expenses_data';
$route['delete-expenses'] = 'Admin_api/delete_expenses';



