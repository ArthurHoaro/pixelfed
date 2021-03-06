<?php

namespace App\Http\Controllers;

use App, Auth, Cache;
use Illuminate\Http\Request;
use App\{Follower, Profile, Status, User};
use App\Util\Lexer\PrettyNumber;

class SiteController extends Controller
{

    public function home()
    {
        if(Auth::check()) {
          return $this->homeTimeline();
        } else {
          return $this->homeGuest();
        }
    }

    public function homeGuest()
    {
        return view('welcome');
    }

    public function homeTimeline()
    {
      // TODO: Use redis for timelines
      $following = Follower::whereProfileId(Auth::user()->profile->id)->pluck('following_id');
      $following->push(Auth::user()->profile->id);
      $timeline = Status::whereIn('profile_id', $following)
                  ->whereHas('media')
                  ->orderBy('id','desc')
                  ->withCount(['comments', 'likes', 'shares'])
                  ->simplePaginate(20);
      $type = 'personal';
      return view('timeline.template', compact('timeline', 'type'));
    }

    public function changeLocale(Request $request, $locale)
    {
        if(!App::isLocale($locale)) {
          return redirect()->back();
        }
        App::setLocale($locale);
        return redirect()->back();
    }

    public function about()
    {
        $res = Cache::remember('site:page:about', 15, function() {
          $statuses = Status::whereHas('media')
              ->whereNull('in_reply_to_id')
              ->whereNull('reblog_of_id')
              ->count();
          $statusCount = PrettyNumber::convert($statuses);
          $userCount = PrettyNumber::convert(User::count());
          $remoteCount = PrettyNumber::convert(Profile::whereNotNull('remote_url')->count());
          $adminContact = User::whereIsAdmin(true)->first();
          return view('site.about')->with(compact('statusCount', 'userCount', 'remoteCount', 'adminContact'))->render();
        });
        return $res;
    }
}
