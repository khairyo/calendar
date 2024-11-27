<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Calendar\AppInfo;

use OCA\Calendar\Dashboard\CalendarWidget;
use OCA\Calendar\Events\BeforeAppointmentBookedEvent;
use OCA\Calendar\Listener\AppointmentBookedListener;
use OCA\Calendar\Listener\CalendarReferenceListener;
use OCA\Calendar\Listener\UserDeletedListener;
use OCA\Calendar\Notification\Notifier;
use OCA\Calendar\Profile\AppointmentsAction;
use OCA\Calendar\Reference\ReferenceProvider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Collaboration\Reference\RenderReferenceEvent;
use OCP\ServerVersion;
use OCP\User\Events\UserDeletedEvent;
use OCP\Util;
use function method_exists;

class Application extends App implements IBootstrap {
	/** @var string */
	public const APP_ID = 'calendar';

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		parent::__construct(self::APP_ID, $params);
	}

	/**
	 * @inheritDoc
	 */
	public function register(IRegistrationContext $context): void {
		$context->registerDashboardWidget(CalendarWidget::class);

		// TODO: drop conditional code when the app is 23+
		if (method_exists($context, 'registerProfileLinkAction')) {
			$context->registerProfileLinkAction(AppointmentsAction::class);
		}
		$context->registerReferenceProvider(ReferenceProvider::class);

		$context->registerEventListener(BeforeAppointmentBookedEvent::class, AppointmentBookedListener::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerEventListener(RenderReferenceEvent::class, CalendarReferenceListener::class);

		$context->registerNotifierService(Notifier::class);
	}

	/**
	 * @inheritDoc
	 */
	public function boot(IBootContext $context): void {
		// TODO: drop condition once we only support Nextcloud >= 31
		/** @var ServerVersion $serverVersion */
		$serverVersion = $context->getServerContainer()->get(ServerVersion::class);
		if ($serverVersion->getMajorVersion() >= 31) {
			// The contacts menu/avatar is potentially shown everywhere so an event based loading
			// mechanism doesn't make sense here
			Util::addScript(self::APP_ID, 'calendar-contacts-menu');
		}
	}
}
