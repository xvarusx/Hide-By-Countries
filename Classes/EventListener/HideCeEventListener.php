<?php

declare(strict_types=1);

namespace Oussema\HideByCountries\EventListener;

use Oussema\HideByCountries\Utility\SessionManagementUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\ContentObject\Event\AfterStdWrapFunctionsExecutedEvent;

final class HideCeEventListener
{
    
    public function __construct(
        private readonly SessionManagementUtility $sessionManagement,
        ){}
    #[AsEventListener(
        identifier: 'hidebycountries/hide-content-element',
    )]
    public function __invoke(AfterStdWrapFunctionsExecutedEvent $event): void
    {
        $cObjectRenderer = $event->getContentObjectRenderer();
        $hiddenCountries = $cObjectRenderer->data['tx_hidebycountries'] ?? '';
        if (!$hiddenCountries) {
            return;
        }
        $userCountry = $this->sessionManagement->getCountryFromSession($event->getContentObjectRenderer()->getRequest());
        
        if ($userCountry) {
            $hiddenCountries = explode(',', $hiddenCountries ?? '');
            if (in_array($userCountry, $hiddenCountries, true)) {
                $event->setContent('');
            }
        }
    }
}
