<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Contracts\DiscordIVAOAuthServiceInterface;
use App\Domain\Contracts\IVAOApiServiceContract;
use App\Domain\Entities\Member;
use App\Exceptions\InvalidPermissionException;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    private $discordIVAOAuth;
    private $IVAOAPI;

    public function __construct(
        DiscordIVAOAuthServiceInterface $discordIVAOAuth,
        IVAOApiServiceContract $IVAOAPI
    ) {
        $this->discordIVAOAuth = $discordIVAOAuth;
        $this->IVAOAPI = $IVAOAPI;
    }

    public function login(Request $request)
    {
        // Require IVAO authentication before Discord OAuth.
        if (!$this->IVAOAPI->getUserData()) {
            $request->session()->put('discord_join_pending', true);

            return redirect('/ivao/login');
        }

        $request->session()->forget('discord_join_pending');

        return Socialite::driver('discord')
            ->scopes(['identify', 'guilds.join'])
            ->redirect();
    }

    public function loginCallback(Request $request)
    {
        if (!$this->IVAOAPI->getUserData()) {
            return redirect('/discord/login');
        }

        if ($request->has('error')) {
            throw new InvalidPermissionException();
        }

        try {
            $user = Socialite::driver('discord')->user();
        } catch (\Exception $e) {
            throw new InvalidPermissionException();
        }

        $member = Member::FromAPIRequest($this->IVAOAPI);

        $member->setDiscordAccessToken($user->token);
        $member->setDiscordId($user->id);

        // Existing, working validation and Discord linking.
        $this->discordIVAOAuth->validateMember($member);

        // Only reached after the linking process succeeds.
        // Keep a short-lived summary for the welcome screen.
        $request->session()->flash('discord_verification', [
            'name' => $member->getFirstName(),
            'vid' => (string) $member->getVid(),
            'division' => $member->getDivision(),
            'roles' => $member->getRoles()->values()->all(),
        ]);

        return redirect('/success');
    }
}