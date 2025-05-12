<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$allowed_origins = [
  'http://localhost:4200',
  'http://localhost:8100',
  'https://shritej.in',
  'https://www.shritej.in',
  'http://shritej.in',
  'https://admin.exiaa.com',
  'https://jisarwa.in',
  'https://www.jisarwa.in',
  'https://realpowershop.com',
  'https://www.realpowershop.com',
  'https://netbugs.in',
  'https://www.netbugs.in',
  'https://netbugs.co.in',
  'https://www.netbugs.co.in',
];
$routes->options('(:any)', function () use ($allowed_origins){
  if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
  }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(204); // No Content
    exit();
});
$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {

  $routes->group('user', function ($routes) {
        $routes->post('login', 'User::login_post');
        $routes->post('test', 'User::test_post');
        $routes->post('pay', 'User::payment_post');
    });

  $routes->group('lead', function ($routes) {
    //Routes for lead
    $routes->get('getall', 'Lead::index',['filter' => ['authFilter', 'tenantFilter']]);
    $routes->post('getallpaging', 'Lead::getLeadsPaging',['filter' => ['authFilter', 'tenantFilter']]);
    $routes->get('view/(:segment)', 'Lead::show/$1',['filter' => 'authFilter']);
    $routes->post('create', 'Lead::create',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('update', 'Lead::update',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('delete', 'Lead::delete',['filter' => ['authFilter','tenantFilter']]);
    $routes->get('getallleadsource', 'Lead::getAllLeadSource',['filter' => ['authFilter','tenantFilter']]);
    $routes->get('getallleadinterest', 'Lead::getAllLeadInterested',['filter' => ['authFilter','tenantFilter']]);
  });

  $routes->group('order', function ($routes) {
    //Routes for order
    $routes->get('getall', 'Order::index',['filter' => ['authFilter', 'tenantFilter']]);
    $routes->post('getallpaging', 'Order::getOrdersPaging',['filter' => ['authFilter', 'tenantFilter']]);
    $routes->post('create', 'Order::create',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('update', 'Order::update',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('delete', 'Order::delete',['filter' => ['authFilter','tenantFilter']]);
    $routes->get('getlastorder', 'Order::getLastOrder',['filter' => ['authFilter','tenantFilter']]);
  });


  $routes->group('tenant', function ($routes) {
    $routes->get('getall', 'Tenant::index',['filter' => ['authFilter']]);
    $routes->post('getallpaging', 'Tenant::getTenantsPaging',['filter' => ['authFilter']]);
    $routes->post('create', 'Tenant::create',['filter' => ['authFilter']]);
    $routes->post('generatedatabase', 'Tenant::generateTenantDatabase',['filter' => ['authFilter']]);
    $routes->post('update', 'Tenant::update',['filter' => ['authFilter']]);
    $routes->post('delete', 'Tenant::delete',['filter' => ['authFilter']]);
  });


});

$routes->group('webapi', ['namespace' => 'App\Controllers'], function ($routes) {

  $routes->group('tenantuser', function ($routes) {
    $routes->post('login', 'TenantUser::loginWithMobileUid',['filter' => 'tenantFilter']);
    $routes->get('profile', 'TenantUser::getProfile',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('create', 'CustomerAddress::create',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('update', 'TenantUser::update',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('create', 'Customer::create',['filter' => ['authFilter','tenantFilter']]);
    $routes->post('add-address', 'CustomerAddress::addAddress', ['filter' => ['authFilter', 'tenantFilter']]);


  });

   $routes->group('iotdevice', function ($routes) {
    $routes->post('add-parameter', 'IotDevice::addParameter', ['filter' => ['tenantFilter']]);
  });


  $routes->group('staff', function ($routes) {
    //Routes for staff
    $routes->get('getall', 'Staff::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Staff::getStaffPaging',['filter' => ['tenantFilter']]);
  });

  $routes->group('item', function ($routes) {
    //Routes for item
    $routes->get('getallcategory', 'Item::getAllItemCategory',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Item::getItemsPaging',['filter' => ['tenantFilter']]);
    $routes->get('getfouritemproductbycategory', 'Item::getFourItemProductByCategory',['filter' => ['tenantFilter']]);
    $routes->post('view', 'Item::show',['filter' => ['tenantFilter']]);
    $routes->post('getallfiltereditem', 'Item::filteredItems',['filter' => ['tenantFilter']]);
  });

  $routes->group('slide', function ($routes) {
    //Routes for gallery
    $routes->get('getall', 'Slide::index',['filter' => ['tenantFilter']]);
  });

  $routes->group('testimonial', function ($routes) {
    //Routes for vendor
    $routes->get('getall', 'Testimonial::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Testimonial::getTestimonialsPaging',['filter' => ['tenantFilter']]);
  });

  $routes->group('blog', function ($routes) {
    //Routes for vendor
    $routes->get('getall', 'Blog::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Blog::getBlogsPaging',['filter' => ['tenantFilter']]);
  });

    $routes->group('portfolio', function ($routes) {
    //Routes for vendor
    $routes->get('getall', 'Portfolio::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Portfolio::getPortfolioPaging',['filter' => ['tenantFilter']]);
  });


  $routes->group('gallery', function ($routes) {
    //Routes for gallery
    $routes->get('getall', 'Gallery::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Gallery::getGallerysPaging',['filter' => ['tenantFilter']]);
  });

  $routes->group('event', function ($routes) {
    //Routes for event
    $routes->get('getall', 'Event::index',['filter' => ['tenantFilter']]);
    $routes->post('getallpaging', 'Event::getEventsPaging',['filter' => ['tenantFilter']]);
  });

});