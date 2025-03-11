@php
    use App\Models\Utility;
    $setting = \App\Models\Utility::settings();
    $logo = \App\Models\Utility::get_file('uploads/logo');

    $company_logo = $setting['company_logo_dark'] ?? '';
    $company_logos = $setting['company_logo_light'] ?? '';
    $company_small_logo = $setting['company_small_logo'] ?? '';

    $emailTemplate = \App\Models\EmailTemplate::emailTemplateData();

    $userPlan = \App\Models\Plan::getPlan(\Auth::user()->show_dashboard());
@endphp
@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
@endphp


<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    @if(!isset($navbarFull))
        <div class="app-brand demo">
        <a href="{{url('/')}}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
        </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>
  
    <!-----****** Company Dashboard Starts Here ******------->
    @if (\Auth::user()->type == 'company')
        <ul class="menu-inner py-1">
            @if (Gate::check('show dashboard'))
                <li class="menu-item {{ Request::segment(1) == null || Request::segment(1) == 'home' ? ' active' : '' }}">
                    <a href="{{ route('home') }}" class="menu-link">
                        <i class="menu-icon ti ti-home"></i>
                        <div data-i18n="Dashboard">{{ __('Dashboard') }}</div>
                    </a>
                </li>
            @endif
            @if (Gate::check('manage lead') || Gate::check('manage form builder') || Gate::check('manage support') || Gate::check('manage client') || Gate::check('manage customer') || Gate::check('manage customer'))
                <li class="menu-item {{ Request::segment(1) == 'stages' || Request::segment(1) == 'customer' ||  Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'support' || Request::segment(1) == 'leads' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'contractType' ||  Request::segment(1) == 'form_response' || Request::segment(1) == 'clients' || Request::segment(1) == 'contract' ? ' active dash-trigger' : '' }}">
                    <a href="#!" class="menu-link  menu-toggle">
                        <i class="menu-icon ti ti-layers-difference"></i>
                        <div data-i18n="CRM System">{{ __('CRM') }}</div>
                    </a>
                    <ul class="menu-sub {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'leads' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' || Request::segment(1) == 'deals' || Request::segment(1) == 'pipelines' ? 'show' : '' }}">
                        @if (Gate::check('manage customer'))
                            <li class="menu-item {{ Request::segment(1) == 'customer' ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('customer.index') }}">{{ __('Clients') }}</a>
                            </li>
                        @endif
                        @can('manage lead')
                            <li class="menu-item {{ Request::route()->getName() == 'leads.list' || Request::route()->getName() == 'leads.index' || Request::route()->getName() == 'leads.show' ? ' active' : '' }}">
                                <a class="menu-link" href="{{ route('leads.index') }}">{{ __('Leads') }}</a>
                            </li>
                        @endcan
                        <li class="menu-item {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                            <a href="{{ route('support.index') }}" class="menu-link">
                                {{ __('Tickets') }}
                            </a>
                        </li>
                         @endif

                        {{--@if (Gate::check('manage lead stage') ||
                            Gate::check('manage pipeline') ||
                            Gate::check('manage source') ||
                            Gate::check('manage label') ||
                            Gate::check('manage stage'))
                            <li
                                class="menu-item  {{ Request::segment(1) == 'stages' || Request::segment(1) == 'labels' || Request::segment(1) == 'sources' || Request::segment(1) == 'lead_stages' || Request::segment(1) == 'pipelines' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'contractType' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' ? 'active dash-trigger' : '' }}">
                                <a class="menu-link"
                                    href="{{ route('pipelines.index') }}   ">{{ __('CRM System Setup') }}</a>

                            </li>
                        @endif--}}
                    </ul>
                </li>
                <!-- <li class="menu-item {{ Request::segment(1) == 'chats' ? 'active' : '' }}">
                    <a href="{{ url('chats') }}" class="menu-link">
                        <i class="menu-icon ti ti-message-circle"></i>
                        <div data-i18n="Companies">{{ __('Messenger') }}</div>
                    </a>
                </li> -->
                @if ((Gate::check('manage messages') || Gate::check('manage bulk messages')))
                    <li class="menu-item {{ Request::segment(1) == 'chats' ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="menu-link menu-toggle">
                            <i class="menu-icon ti message-circle"></i>
                            <div data-i18n="Messages">{{ __('Messages') }}</div>
                        </a>
                        <ul class="menu-sub">
                            @can('manage messages')
                                <li class="menu-item {{ Request::route()->getName() == 'chat.index' || Request::route()->getName() == 'chats.create' || Request::route()->getName() == 'users.edit' || Request::route()->getName() == 'user.userlog' ? ' active' : '' }}">
                                    <a class="menu-link" href="{{ url('chats') }}">{{ __('Send Email') }}</a>
                                </li>
                            @endcan
                            @can('manage bulk chats')
                                <li class="menu-item {{ Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit' ? ' active' : '' }} ">
                                    <a class="menu-link" href="{{ url('chats') }}">{{ __('Bulk Customers') }}</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif
                @if (Gate::check('manage bank account') || Gate::check('manage bank transfer') || Gate::check('manage invoice') ||
                    Gate::check('manage revenue') ||  Gate::check('manage payment'))
                    <li class="menu-item {{ Request::route()->getName() == 'print-setting' || Request::segment(1) == 'bank-account' || Request::segment(1) == 'bank-transfer' ||
                        Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue' || Request::segment(1) == 'payment-method' || (Request::segment(1) == 'transaction') ||
                        Request::segment(1) == 'expense' || Request::segment(1) == 'payment' ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="menu-link menu-toggle">
                            <i class="menu-icon ti ti-box"></i>
                            <div data-i18n="Reports">{{ __('Reports ') }}</div>
                        </a>
                        <ul class="menu-sub">
                            @if (Gate::check('manage proposal') || Gate::check('manage invoice') || Gate::check('manage revenue') || Gate::check('manage credit note'))
                                    <li class="menu-item {{ Request::route()->getName() == 'revenue.index' || Request::route()->getName() == 'revenue.create' || Request::route()->getName() == 'revenue.edit' ? ' active' : '' }}">
                                        <a class="menu-link" href="{{ route('revenue.index') }}">{{ __('Daily Transactions') }}</a>
                                    </li>
                                    <li class="menu-item {{ Request::route()->getName() == 'revenue.index' || Request::route()->getName() == 'revenue.create' || Request::route()->getName() == 'revenue.edit' ? ' active' : '' }}">
                                        <a class="menu-link" href="{{ route('revenue.index') }}">{{ __('Period Transactions') }}</a>
                                    </li>
                                    <li class="menu-item {{ Request::route()->getName() == 'invoice.index' || Request::route()->getName() == 'invoice.create' || Request::route()->getName() == 'invoice.edit' || Request::route()->getName() == 'invoice.show' ? ' active' : '' }}">
                                        <a class="menu-link" href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
                                    </li>
                                    
                            @endif
                            @if ( Gate::check('manage payment') )
                                <li class="menu-item {{ Request::route()->getName() == 'expense.index' || Request::route()->getName() == 'expense.create' || Request::route()->getName() == 'expense.edit' || Request::route()->getName() == 'expense.show' ? ' active' : '' }}">
                                    <a class="menu-link" href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                                </li>
                                <li class="menu-item {{ Request::route()->getName() == 'payment.index' || Request::route()->getName() == 'payment.create' || Request::route()->getName() == 'payment.edit' ? ' active' : '' }}">
                                    <a class="menu-link" href="{{ route('payment.index') }}">{{ __('Payment') }}</a>
                                </li>
                            @endif
                            
                            @if (Gate::check('manage constant tax') ||
                                    Gate::check('manage constant payment method') ||
                                    Gate::check('manage constant custom field'))
                                <li class="menu-item {{ Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type' ? 'active dash-trigger' : '' }}">
                                    <a class="menu-link" href="{{ route('taxes.index') }}">{{ __('Tax Setup') }}</a>
                                </li>
                            @endif

                            @if (Gate::check('manage print settings'))
                                <li
                                    class="menu-item {{ Request::route()->getName() == 'print-setting' ? ' active' : '' }}">
                                    <a class="menu-link"
                                        href="{{ route('print.setting') }}">{{ __('Print Settings') }}</a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
        
            <li class="menu-header small">
                <span class="menu-header-text" data-i18n="NETWORK">NETWORK</span>
            </li>
            @if ((Gate::check('manage package') || Gate::check('manage fup') || Gate::check('manage nas') || Gate::check('manage tr069')))
            <li class="menu-item {{ Request::segment(1) == 'package' ? 'active' : '' }}">
                <a href="{{ route('packages.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-packages"></i>
                    <div data-i18n="Packages">{{ __('Packages') }}</div>
                </a>
            </li>
            <li class="menu-item {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                <a href="{{ route('support.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-brand-speedtest"></i>
                    <div data-i18n="FUP">{{ __('FUP') }}</div>
                </a>
            </li>
            <li class="menu-item {{ Request::segment(1) == 'nas' ? 'active' : '' }}">
                <a href="{{ route('nas.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-server-2"></i>
                    <div data-i18n="Sites">{{ __('Sites') }}</div>
                </a>
            </li>
            <li class="menu-item {{ Request::segment(1) == 'support' ? 'active' : '' }}">
                <a href="{{ route('support.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-brand-databricks"></i>
                    <div data-i18n="TR069">{{ __('TR069') }}</div>
                </a>
            </li>
            @endif
            <li class="menu-header small">
                <span class="menu-header-text" data-i18n="SYSTEM SETUP">SYSTEM SETUP</span>
            </li>
            @if ((Gate::check('manage user') || Gate::check('manage role')))
                <li class="menu-item {{ Request::segment(1) == 'users' || Request::segment(1) == 'roles' || Request::segment(1) == 'userlogs' ? ' active dash-trigger' : '' }}">
                    <a href="#!" class="menu-link menu-toggle">
                        <i class="menu-icon ti ti-users"></i>
                        <div data-i18n="System Users">{{ __('System Users') }}</div>
                    </a>
                    <ul class="menu-sub">
                        @can('manage user')
                            <li class="menu-item {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' || Request::route()->getName() == 'user.userlog' ? ' active' : '' }}">
                                <a class="menu-link" href="{{ route('users.index') }}">{{ __('User') }}</a>
                            </li>
                        @endcan
                        @can('manage role')
                            <li class="menu-item {{ Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit' ? ' active' : '' }} ">
                                <a class="menu-link" href="{{ route('roles.index') }}">{{ __('Role') }}</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            <li class="menu-item {{ Request::segment(1) == 'notification_templates' ? 'active' : '' }}">
                <a href="{{ route('notification-templates.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-notification"></i>
                    <div data-i18n="Notification Template">{{ __('Notification Template') }}</div>
                </a>
            </li>
            @if (Gate::check('manage company plan') || Gate::check('manage order') || Gate::check('manage company settings'))
                <li class="menu-item {{ Request::segment(1) == 'settings' || Request::segment(1) == 'plans' || Request::segment(1) == 'stripe' || Request::segment(1) == 'order' ? ' active dash-trigger' : '' }}">
                    <a href="#!" class="menu-link menu-toggle">
                        <i class="menu-icon ti ti-settings"></i>
                        <div data-i18n="Settings">{{ __('Settings') }}</div>
                    </a>
                    <ul class="menu-sub">
                        @if (Gate::check('manage company settings'))
                            <li class="menu-item {{ Request::segment(1) == 'settings' ? ' active' : '' }}">
                                <a href="{{ route('settings') }}" class="menu-link">{{ __('System Settings') }}</a>
                            </li>
                        @endif
                        @if (Gate::check('manage company plan'))
                            <li class="menu-item{{ Request::route()->getName() == 'plans.index' || Request::route()->getName() == 'stripe' ? ' active' : '' }}">
                                <a href="{{ route('plans.index') }}" class="menu-link">{{ __('Subscription Plan') }}</a>
                            </li>
                        @endif
                        <li
                        class="menu-item{{ Request::route()->getName() == 'referral-program.company' ? ' active' : '' }}">
                        <a href="{{ route('referral-program.company') }}" class="menu-link">{{ __('Referral Program') }}</a>
                        </li>

                        @if (Gate::check('manage order') && Auth::user()->type == 'company')
                            <li class="menu-item {{ Request::segment(1) == 'order' ? 'active' : '' }}">
                                <a href="{{ route('order.index') }}" class="menu-link">{{ __('Orders') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    @endif
    <!-----****** Company Dashboard Ends Here ******------->

    <!-----****** Super Admin Dashboard Starts Here ******------>
    @if (\Auth::user()->type == 'super admin')
        <ul class="menu-inner py-1">
            @if (Gate::check('manage super admin dashboard'))
                <li class="menu-item  {{ Request::segment(1) == 'dashboard' ? ' active' : '' }}">
                    <a href="{{ route('client.dashboard.view') }}" class="menu-link">
                        <i class="menu-icon ti ti-home"></i>
                        <div data-i18n="Dashboard">{{ __('Dashboard') }}</div>
                    </a>
                </li>
            @endif

            <li class="menu-header small">
                <span class="menu-header-text" data-i18n="MANAGEMENT">MANAGEMENT</span>
            </li>
            @can('manage user')
                <li
                    class="menu-item {{ Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit' ? ' active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon ti ti-users"></i>
                        <div data-i18n="Companies">{{ __('Companies') }}</div>
                    </a>
                </li>
            @endcan

            @if (Gate::check('manage plan'))
                <li class="menu-item  {{ Request::segment(1) == 'plans' ? 'active' : '' }}">
                    <a href="{{ route('plans.index') }}" class="menu-link">
                        <i class="menu-icon ti ti-trophy"></i>
                        <div data-i18n="Plans">{{ __('Plans') }}</div>
                    </a>
                </li>
            @endif
            @if (\Auth::user()->type == 'super admin')
            <li class="menu-item {{ request()->is('plan_request*') ? 'active' : '' }}">
                <a href="{{ route('plan_request.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-arrow-up-right-circle"></i>
                    <div data-i18n="Plan Requests">{{ __('Plan Requests') }}</div>
                </a>
            </li>
            @endif

            @if (Gate::check('manage order'))
                <li class="menu-item  {{ Request::segment(1) == 'orders' ? 'active' : '' }}">
                    <a href="{{ route('order.index') }}" class="menu-link">
                        <i class="menu-icon ti ti-shopping-cart-plus"></i>
                        <div data-i18n="Orders">{{ __('Orders') }}</div>
                    </a>
                </li>
            @endif
            <li class="menu-header small">
                <span class="menu-header-text" data-i18n="SYSTEM SETUP">SYSTEM SETUP</span>
            </li>
            <li class="menu-item  {{ Request::segment(1) == '' ? 'active' : '' }}">
                <a href="{{ route('permissions.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-shield"></i>
                    <div data-i18n="Referral Program">{{ __('Permissions') }}</div>
                </a>
            </li>
            <li class="menu-item  {{ Request::segment(1) == '' ? 'active' : '' }}">
                <a href="{{ route('referral-program.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-discount-2"></i>
                    <div data-i18n="Referral Program">{{ __('Referral Program') }}</div>
                </a>
            </li>
            @if (Gate::check('manage coupon'))
                <li class="menu-item {{ Request::segment(1) == 'coupons' ? 'active' : '' }}">
                    <a href="{{ route('coupons.index') }}" class="menu-link">
                        <i class="menu-icon ti ti-gift"></i>
                        <div data-i18n="Coupons">{{ __('Coupons') }}</div>
                    </a>
                </li>
            @endif
            <li
                class="menu-item {{ Request::segment(1) == 'email_template' || Request::route()->getName() == 'manage.email.language' ? ' active dash-trigger' : 'collapsed' }}">
                <a href="{{ route('email_template.index') }}" class="menu-link">
                    <i class="menu-icon ti ti-template"></i>
                    <div data-i18n="Email Templates">{{ __('Email Template') }}</div>
                </a>
            </li>

            {{-- @if (\Auth::user()->type == 'super admin')
                @include('landingpage::menu.landingpage')
            @endif --}}

            @if (Gate::check('manage system settings'))
                <li class="menu-item {{ Request::route()->getName() == 'systems.index' ? ' active' : '' }}">
                    <a href="{{ route('systems.index') }}" class="menu-link">
                        <i class="menu-icon ti ti-settings"></i>
                        <div data-i18n="Settings">{{ __('Settings') }}</div>
                    </a>
                </li>
            @endif

        </ul>
    @endif
    <!-----****** Super Admin Dashboard Ends Here ******------>
</aside>
