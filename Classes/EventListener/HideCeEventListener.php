<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\ContentObject\Event\AfterStdWrapFunctionsExecutedEvent;

final class HideCeEventListener
{

#[AsEventListener(
    identifier: 'HideByCountries/hide-content-element',
)]
    public function __invoke(AfterStdWrapFunctionsExecutedEvent $event): void
    {
        $cObjectRenderer = $event->getContentObjectRenderer();
        $hiddenCountries = $cObjectRenderer->data['tx_hidebycountries'] ?? '';
        if(!$hiddenCountries) {
            return;
        }
        $userCountry = $event->getContentObjectRenderer()->getRequest()->getCookieParams()['user_country'] ?? null;
        if($userCountry){
            $hiddenCountries = explode(',', $hiddenCountries ?? '');
            if(in_array($userCountry, $hiddenCountries, true)){
                $event->setContent('');
            }
        }
    }
}