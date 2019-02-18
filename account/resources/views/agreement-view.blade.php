@extends('trax-ui::layouts.int')

@section('body-class')
    trax-account-agreement-view-page
@endsection

@section('page')

<div class="row">
    <div class="col-12">
        <trax-account-agreement></trax-account-agreement>
    </div>
</div>

@endsection

@section('components')
    <script src="{{ traxMix('js/trax-account.js') }}"></script>
@endsection
