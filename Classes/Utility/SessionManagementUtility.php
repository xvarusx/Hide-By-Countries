<?php

namespace Oussema\HideByCountries\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
class SessionManagementUtility {
    private const COOKIE_NAME = 'fe_user_country';
    private function getFrontendUser(ServerRequestInterface $request): FrontendUserAuthentication
    {
        // This will create an anonymous frontend user if none is logged in
        return $request->getAttribute('frontend.user');
    }
    public function storeCountryInSession(string $countrycode,ServerRequestInterface $request): void
    {
        // We use type ses to store the data in the session
        $this->getFrontendUser($request)->setKey('ses', self::COOKIE_NAME, serialize($countrycode));
        // Important: store session data! Or it is not available in the next request!
        $this->getFrontendUser($request)->storeSessionData();
    }
    public function getCountryFromSession(ServerRequestInterface $request): ?String
    {
        $data = $this->getFrontendUser($request)->getKey('ses', self::COOKIE_NAME);
        if (is_string($data)) {
            $country = unserialize($data);
            if ($country) {
                return $country;
            }
        }
        return null;
    }
}
