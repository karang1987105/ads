@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ __('You are logged in!') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!doctype html>
<html class="no-js h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link href="{{ asset('css/pre-app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="h-100">
<div class="container-fluid">
    <div class="row">
        <!-- Main Sidebar -->
        <aside class="main-sidebar col-12 col-md-3 col-lg-2 px-0">
            <div class="main-navbar">
                <nav class="navbar align-items-stretch navbar-light bg-white flex-md-nowrap border-bottom p-0">
                    <a class="navbar-brand w-100 mr-0" href="#" style="line-height: 25px;">
                        <div class="d-table m-auto">
                            <img id="main-logo" class="d-inline-block align-top mr-1" style="max-width: 25px;"
                                 src="images/shards-dashboards-logo-warning.svg" alt="Shards Dashboard">
                            <span class="d-none d-md-inline ml-1">Shards Dashboard</span>
                        </div>
                    </a>
                    <a class="toggle-sidebar d-sm-inline d-md-none d-lg-none">
                        <i class="material-icons">&#xE5C4;</i>
                    </a>
                </nav>
            </div>
            <form action="#" class="main-sidebar__search w-100 border-right d-sm-flex d-md-none d-lg-none">
                <div class="input-group input-group-seamless ml-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                    <input class="navbar-search form-control" type="text" placeholder="Search for something..."
                           aria-label="Search"></div>
            </form>
            <div class="nav-wrapper">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dashboard') }}">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    @userType('admin', 'manager("advertisers","publishers")')
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#">
                            <i class="material-icons">people</i>
                            <span>Users</span>
                        </a>
                        <ul class="nav flex-column">
                            @admin
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">supervisor_account</i>
                                    <span>Managers</span>
                                </a>
                            </li>
                            @endAdmin
                            @userType('admin', 'manager("advertisers")')
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">shopping_bag</i>
                                    <span>Advertisers</span>
                                </a>
                            </li>
                            @endUserType
                            @userType('admin', 'manager("publishers")')
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">storefront</i>
                                    <span>Publishers</span>
                                </a>
                            </li>
                            @endUserType
                        </ul>
                    </li>
                    @endUserType
                    @userType('admin', 'advertiser', 'publisher')
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#">
                            <i class="material-icons">account_balance</i>
                            <span>Accounting</span>
                        </a>
                        <ul class="nav flex-column">
                            @admin
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">euro</i>
                                    <span>Currencies</span>
                                </a>
                            </li>
                            @endAdmin
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">receipt</i>
                                    <span>Invoices</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="material-icons">payment</i>
                                    <span>Payments</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endUserType
                    @userType('admin', 'advertiser', 'manager("promos")')
                    <li class="nav-item">
                        <a class="nav-link " href="add-new-post.html">
                            <i class="material-icons">local_offer</i>
                            <span>Promos</span>
                        </a>
                    </li>
                    @endUserType
                    @admin
                    <li class="nav-item">
                        <a class="nav-link " href="form-components.html">
                            <i class="material-icons">public</i>
                            <span>Geo Profiles</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="tables.html">
                            <i class="material-icons">format_list_bulleted</i>
                            <span>Ad-Types</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="user-profile-lite.html">
                            <i class="material-icons">category</i>
                            <span>Categories</span>
                        </a>
                    </li>
                    @endAdmin
                    @userType('admin', 'publisher')
                    <li class="nav-item">
                        <a class="nav-link " href="errors.html">
                            <i class="material-icons">holiday_village</i>
                            <span>Places</span>
                        </a>
                    </li>
                    @endUserType
                    @userType('admin', 'advertiser', 'manager("advertisements")')
                    <li class="nav-item">
                        <a class="nav-link " href="errors.html">
                            <i class="material-icons">ads_click</i>
                            <span>Ads</span>
                        </a>
                    </li>
                    @endUserType
                </ul>
            </div>
        </aside>
        <!-- End Main Sidebar -->
        <main class="main-content col-lg-10 col-md-9 col-sm-12 p-0 offset-lg-2 offset-md-3">
            <div class="main-navbar sticky-top bg-white">
                <!-- Main Navbar -->
                <nav class="navbar align-items-stretch navbar-light flex-md-nowrap p-0">
                    <form action="#" class="main-navbar__search w-100 d-none d-md-flex d-lg-flex">
                        <div class="input-group input-group-seamless ml-3">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                            <input class="navbar-search form-control" type="text" placeholder="Search for something..."
                                   aria-label="Search"></div>
                    </form>
                    <ul class="navbar-nav border-left flex-row ">
                        <li class="nav-item border-right dropdown notifications">
                            <a class="nav-link nav-link-icon text-center" href="#" role="button" id="dropdownMenuLink"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="nav-link-icon__wrapper">
                                    <i class="material-icons">&#xE7F4;</i>
                                    <span class="badge badge-pill badge-danger">2</span>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-small" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="#">
                                    <div class="notification__icon-wrapper">
                                        <div class="notification__icon">
                                            <i class="material-icons">&#xE6E1;</i>
                                        </div>
                                    </div>
                                    <div class="notification__content">
                                        <span class="notification__category">Analytics</span>
                                        <p>Your website’s active users count increased by
                                            <span class="text-success text-semibold">28%</span> in the last week. Great
                                            job!</p>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="#">
                                    <div class="notification__icon-wrapper">
                                        <div class="notification__icon">
                                            <i class="material-icons">&#xE8D1;</i>
                                        </div>
                                    </div>
                                    <div class="notification__content">
                                        <span class="notification__category">Sales</span>
                                        <p>Last week your store’s sales count decreased by
                                            <span class="text-danger text-semibold">5.52%</span>. It could have been
                                            worse!</p>
                                    </div>
                                </a>
                                <a class="dropdown-item notification__all text-center" href="#"> View all
                                    Notifications </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-nowrap px-3" data-toggle="dropdown" href="#"
                               role="button" aria-haspopup="true" aria-expanded="false">
                                <img class="user-avatar rounded-circle mr-2" src="images/avatars/0.jpg"
                                     alt="User Avatar">
                                <span class="d-none d-md-inline-block">Sierra Brooks</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-small">
                                <a class="dropdown-item" href="user-profile-lite.html">
                                    <i class="material-icons">&#xE7FD;</i> Profile</a>
                                <a class="dropdown-item" href="components-blog-posts.html">
                                    <i class="material-icons">vertical_split</i> Blog Posts</a>
                                <a class="dropdown-item" href="add-new-post.html">
                                    <i class="material-icons">note_add</i> Add New Post</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#">
                                    <i class="material-icons text-danger">&#xE879;</i> Logout </a>
                            </div>
                        </li>
                    </ul>
                    <nav class="nav">
                        <a href="#"
                           class="nav-link nav-link-icon toggle-sidebar d-md-inline d-lg-none text-center border-left"
                           data-toggle="collapse" data-target=".header-navbar" aria-expanded="false"
                           aria-controls="header-navbar">
                            <i class="material-icons">&#xE5D2;</i>
                        </a>
                    </nav>
                </nav>
            </div>
            <!-- / .main-navbar -->
            <div class="main-content-container container-fluid px-4">
                <!-- Page Header -->
                <div class="page-header row no-gutters py-4">
                    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
                        <span class="text-uppercase page-subtitle">Dashboard</span>
                        <h3 class="page-title">Blog Overview</h3>
                    </div>
                </div>
                <!-- End Page Header -->
                <!-- Small Stats Blocks -->
                <div class="row">
                    <div class="col-lg col-md-6 col-sm-6 mb-4">
                        <div class="stats-small stats-small--1 card card-small">
                            <div class="card-body p-0 d-flex">
                                <div class="d-flex flex-column m-auto">
                                    <div class="stats-small__data text-center">
                                        <span class="stats-small__label text-uppercase">Posts</span>
                                        <h6 class="stats-small__value count my-3">2,390</h6>
                                    </div>
                                    <div class="stats-small__data">
                                        <span
                                            class="stats-small__percentage stats-small__percentage--increase">4.7%</span>
                                    </div>
                                </div>
                                <canvas height="120" class="blog-overview-stats-small-1"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg col-md-6 col-sm-6 mb-4">
                        <div class="stats-small stats-small--1 card card-small">
                            <div class="card-body p-0 d-flex">
                                <div class="d-flex flex-column m-auto">
                                    <div class="stats-small__data text-center">
                                        <span class="stats-small__label text-uppercase">Pages</span>
                                        <h6 class="stats-small__value count my-3">182</h6>
                                    </div>
                                    <div class="stats-small__data">
                                        <span
                                            class="stats-small__percentage stats-small__percentage--increase">12.4%</span>
                                    </div>
                                </div>
                                <canvas height="120" class="blog-overview-stats-small-2"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg col-md-4 col-sm-6 mb-4">
                        <div class="stats-small stats-small--1 card card-small">
                            <div class="card-body p-0 d-flex">
                                <div class="d-flex flex-column m-auto">
                                    <div class="stats-small__data text-center">
                                        <span class="stats-small__label text-uppercase">Comments</span>
                                        <h6 class="stats-small__value count my-3">8,147</h6>
                                    </div>
                                    <div class="stats-small__data">
                                        <span
                                            class="stats-small__percentage stats-small__percentage--decrease">3.8%</span>
                                    </div>
                                </div>
                                <canvas height="120" class="blog-overview-stats-small-3"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg col-md-4 col-sm-6 mb-4">
                        <div class="stats-small stats-small--1 card card-small">
                            <div class="card-body p-0 d-flex">
                                <div class="d-flex flex-column m-auto">
                                    <div class="stats-small__data text-center">
                                        <span class="stats-small__label text-uppercase">Users</span>
                                        <h6 class="stats-small__value count my-3">2,413</h6>
                                    </div>
                                    <div class="stats-small__data">
                                        <span
                                            class="stats-small__percentage stats-small__percentage--increase">12.4%</span>
                                    </div>
                                </div>
                                <canvas height="120" class="blog-overview-stats-small-4"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg col-md-4 col-sm-12 mb-4">
                        <div class="stats-small stats-small--1 card card-small">
                            <div class="card-body p-0 d-flex">
                                <div class="d-flex flex-column m-auto">
                                    <div class="stats-small__data text-center">
                                        <span class="stats-small__label text-uppercase">Subscribers</span>
                                        <h6 class="stats-small__value count my-3">17,281</h6>
                                    </div>
                                    <div class="stats-small__data">
                                        <span
                                            class="stats-small__percentage stats-small__percentage--decrease">2.4%</span>
                                    </div>
                                </div>
                                <canvas height="120" class="blog-overview-stats-small-5"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Small Stats Blocks -->
                <div class="row">
                    <!-- Users Stats -->
                    <div class="col-lg-8 col-md-12 col-sm-12 mb-4">
                        <div class="card card-small">
                            <div class="card-header border-bottom">
                                <h6 class="m-0">Users</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row border-bottom py-2 bg-light">
                                    <div class="col-12 col-sm-6">
                                        <div id="blog-overview-date-range"
                                             class="input-daterange input-group input-group-sm my-auto ml-auto mr-auto ml-sm-auto mr-sm-0"
                                             style="max-width: 350px;">
                                            <input type="text" class="input-sm form-control" name="start"
                                                   placeholder="Start Date" id="blog-overview-date-range-1">
                                            <input type="text" class="input-sm form-control" name="end"
                                                   placeholder="End Date" id="blog-overview-date-range-2">
                                            <span class="input-group-append">
                            <span class="input-group-text">
                              <i class="material-icons"></i>
                            </span>
                          </span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 d-flex mb-2 mb-sm-0">
                                        <button type="button"
                                                class="btn btn-sm btn-white ml-auto mr-auto ml-sm-auto mr-sm-0 mt-3 mt-sm-0">
                                            View Full Report &rarr;
                                        </button>
                                    </div>
                                </div>
                                <canvas height="130" style="max-width: 100% !important;"
                                        class="blog-overview-users"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- End Users Stats -->
                    <!-- Users By Device Stats -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card card-small h-100">
                            <div class="card-header border-bottom">
                                <h6 class="m-0">Users by device</h6>
                            </div>
                            <div class="card-body d-flex py-0">
                                <canvas height="220" class="blog-users-by-device m-auto"></canvas>
                            </div>
                            <div class="card-footer border-top">
                                <div class="row">
                                    <div class="col">
                                        <select class="custom-select custom-select-sm" style="max-width: 130px;">
                                            <option selected>Last Week</option>
                                            <option value="1">Today</option>
                                            <option value="2">Last Month</option>
                                            <option value="3">Last Year</option>
                                        </select>
                                    </div>
                                    <div class="col text-right view-report">
                                        <a href="#">Full report &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Users By Device Stats -->
                    <!-- New Draft Component -->
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <!-- Quick Post -->
                        <div class="card card-small h-100">
                            <div class="card-header border-bottom">
                                <h6 class="m-0">New Draft</h6>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <form class="quick-post-form">
                                    <div class="form-group">
                                        <input type="email" class="form-control" id="exampleInputEmail1"
                                               aria-describedby="emailHelp" placeholder="Brave New World"></div>
                                    <div class="form-group">
                                        <textarea class="form-control"
                                                  placeholder="Words can be like X-rays if you use them properly..."></textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-accent">Create Draft</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- End Quick Post -->
                    </div>
                    <!-- End New Draft Component -->
                    <!-- Discussions Component -->
                    <div class="col-lg-5 col-md-12 col-sm-12 mb-4">
                        <div class="card card-small blog-comments">
                            <div class="card-header border-bottom">
                                <h6 class="m-0">Discussions</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="blog-comments__item d-flex p-3">
                                    <div class="blog-comments__avatar mr-3">
                                        <img src="images/avatars/1.jpg" alt="User avatar"/></div>
                                    <div class="blog-comments__content">
                                        <div class="blog-comments__meta text-muted">
                                            <a class="text-secondary" href="#">James Johnson</a> on
                                            <a class="text-secondary" href="#">Hello World!</a>
                                            <span class="text-muted">– 3 days ago</span>
                                        </div>
                                        <p class="m-0 my-1 mb-2 text-muted">Well, the way they make shows is, they make
                                            one show ...</p>
                                        <div class="blog-comments__actions">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-white">
                              <span class="text-success">
                                <i class="material-icons">check</i>
                              </span> Approve
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-danger">
                                <i class="material-icons">clear</i>
                              </span> Reject
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-light">
                                <i class="material-icons">more_vert</i>
                              </span> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="blog-comments__item d-flex p-3">
                                    <div class="blog-comments__avatar mr-3">
                                        <img src="images/avatars/2.jpg" alt="User avatar"/></div>
                                    <div class="blog-comments__content">
                                        <div class="blog-comments__meta text-muted">
                                            <a class="text-secondary" href="#">James Johnson</a> on
                                            <a class="text-secondary" href="#">Hello World!</a>
                                            <span class="text-muted">– 4 days ago</span>
                                        </div>
                                        <p class="m-0 my-1 mb-2 text-muted">After the avalanche, it took us a week to
                                            climb out. Now...</p>
                                        <div class="blog-comments__actions">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-white">
                              <span class="text-success">
                                <i class="material-icons">check</i>
                              </span> Approve
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-danger">
                                <i class="material-icons">clear</i>
                              </span> Reject
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-light">
                                <i class="material-icons">more_vert</i>
                              </span> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="blog-comments__item d-flex p-3">
                                    <div class="blog-comments__avatar mr-3">
                                        <img src="images/avatars/3.jpg" alt="User avatar"/></div>
                                    <div class="blog-comments__content">
                                        <div class="blog-comments__meta text-muted">
                                            <a class="text-secondary" href="#">James Johnson</a> on
                                            <a class="text-secondary" href="#">Hello World!</a>
                                            <span class="text-muted">– 5 days ago</span>
                                        </div>
                                        <p class="m-0 my-1 mb-2 text-muted">My money's in that office, right? If she
                                            start giving me...</p>
                                        <div class="blog-comments__actions">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-white">
                              <span class="text-success">
                                <i class="material-icons">check</i>
                              </span> Approve
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-danger">
                                <i class="material-icons">clear</i>
                              </span> Reject
                                                </button>
                                                <button type="button" class="btn btn-white">
                              <span class="text-light">
                                <i class="material-icons">more_vert</i>
                              </span> Edit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer border-top">
                                <div class="row">
                                    <div class="col text-center view-report">
                                        <button type="submit" class="btn btn-white">View All Comments</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Discussions Component -->
                    <!-- Top Referrals Component -->
                    <div class="col-lg-3 col-md-12 col-sm-12 mb-4">
                        <div class="card card-small">
                            <div class="card-header border-bottom">
                                <h6 class="m-0">Top Referrals</h6>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-small list-group-flush">
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">GitHub</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">19,291</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">Stack Overflow</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">11,201</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">Hacker News</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">9,291</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">Reddit</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">8,281</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">The Next Web</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">7,128</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">Tech Crunch</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">6,218</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">YouTube</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">1,218</span>
                                    </li>
                                    <li class="list-group-item d-flex px-3">
                                        <span class="text-semibold text-fiord-blue">Adobe</span>
                                        <span class="ml-auto text-right text-semibold text-reagent-gray">827</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer border-top">
                                <div class="row">
                                    <div class="col">
                                        <select class="custom-select custom-select-sm">
                                            <option selected>Last Week</option>
                                            <option value="1">Today</option>
                                            <option value="2">Last Month</option>
                                            <option value="3">Last Year</option>
                                        </select>
                                    </div>
                                    <div class="col text-right view-report">
                                        <a href="#">Full report &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Top Referrals Component -->
                </div>
            </div>
            <footer class="main-footer d-flex p-2 px-3 bg-white border-top">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Blog</a>
                    </li>
                </ul>
                <span class="copyright ml-auto my-auto mr-2">Copyright © 2021
            </span>
            </footer>
        </main>
    </div>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>

