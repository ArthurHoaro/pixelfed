@extends('settings.template')

@section('section')

  <div class="title">
    <h3 class="font-weight-bold">Privacy Settings</h3>
  </div>
  <hr>
  <form method="post">
    @csrf
    <div class="form-check pb-3">
      <input class="form-check-input" type="checkbox" name="is_private" id="is_private" {{$settings->is_private ? 'checked=""':''}}>
      <label class="form-check-label font-weight-bold" for="is_private">
        {{__('Private Account')}}
      </label>
      <p class="text-muted small help-text">When your account is private, only people you approve can see your photos and videos on pixelfed. Your existing followers won't be affected.</p>
    </div>
    <div class="form-check pb-3">
      <input class="form-check-input" type="checkbox" name="crawlable" id="crawlable" {{!$settings->crawlable ? 'checked=""':''}} {{$settings->is_private ? 'disabled=""':''}}>
      <label class="form-check-label font-weight-bold" for="crawlable">
        {{__('Opt-out of search engine indexing')}}
      </label>
      <p class="text-muted small help-text">When your account is visible to search engines, your information can be crawled and stored by search engines.</p>
    </div>



    <div class="form-group row mt-5 pt-5">
      <div class="col-12 text-right">
        <hr>
        <button type="submit" class="btn btn-primary font-weight-bold">Submit</button>
      </div>
    </div>
  </form>

@endsection