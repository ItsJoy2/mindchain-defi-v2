@extends('admin.layouts.app')

@section('title', 'Investment Settings')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="card">
        <div class="card-header">
            <strong>Investment Settings</strong>
            </div>
    </div>

    {{-- Alert --}}
    @include('admin.components.alerts')

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            {{-- Tabs --}}
            <ul class="nav nav-pills mb-4" id="settingTabs" role="tablist">


                <li class="nav-item me-2">
                    <button class="nav-link active" data-coreui-toggle="tab" data-coreui-target="#mind" type="button">

                        MIND

                    </button>
                </li>

                <li class="nav-item me-2">
                    <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#bmind" type="button">

                        BMIND

                    </button>
                </li>

                <li class="nav-item me-2">
                    <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#musd" type="button">
                        MUSD

                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#angel" type="button">

                        Angel

                    </button>
                </li>

                <li class="nav-item me-2">
                    <button
                        class="nav-link"  data-coreui-toggle="tab" data-coreui-target="#elite"  type="button">
                        Elite

                    </button>
                </li>

                <li class="nav-item me-2">
                    <button  class="nav-link"
                        data-coreui-toggle="tab" data-coreui-target="#elitev2" type="button">
                        Elite V2

                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#mkids" type="button">

                        MKIDS

                    </button>
                </li>

            </ul>

            {{-- Tab Content --}}
            <div class="tab-content">

                <div class="tab-pane fade show active" id="mind">
                    @include('admin.pages.settings.sections.mind')
                </div>

                <div class="tab-pane fade" id="bmind">
                    @include('admin.pages.settings.sections.bmind')
                </div>

                <div class="tab-pane fade" id="musd">
                    @include('admin.pages.settings.sections.musd')
                </div>

                <div class="tab-pane fade" id="angel">
                    @include('admin.pages.settings.sections.angel')
                </div>

                <div class="tab-pane fade" id="elite">
                    @include('admin.pages.settings.sections.elite')
                </div>

                <div class="tab-pane fade" id="elitev2">
                    @include('admin.pages.settings.sections.elite-v2')
                </div>

                <div class="tab-pane fade" id="mkids">
                    @include('admin.pages.settings.sections.mkids')
                </div>

            </div>

        </div>

    </div>

</div>

@endsection


    <style>

    .nav-pills .nav-link{
        border-radius:10px;
        padding:.7rem 1.25rem;
        font-weight:600;
        color:#0079e2;
        transition:.2s;
    }

    .nav-pills .nav-link:hover{
        background:#f0cd09 !important;
        color:#0079e2 !important;
    }

    .nav-pills .nav-link.active{
        background:#f0cd09 !important;
        color:#0079e2 !important;
        box-shadow:0 4px 12px rgba(50,31,219,.25);
    }

    .tab-content{
        padding-top:10px;
    }

        .card{
        border-radius:16px;
    }

    .form-control,
    .form-select,
    .input-group-text{
        border-radius:10px;
    }

    .form-control,
    .form-select{
        min-height:45px;
    }

    .form-label{
        margin-bottom:.55rem;
    }

    .btn-primary{
        border-radius:10px;
        padding:10px 30px;
        font-weight:600;
    }



    hr{
        opacity:.08;
    }
</style>

