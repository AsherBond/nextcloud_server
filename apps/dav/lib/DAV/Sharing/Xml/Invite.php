<?php

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-FileCopyrightText: fruux GmbH (https://fruux.com/)
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\DAV\DAV\Sharing\Xml;

use OCA\DAV\DAV\Sharing\Plugin;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Invite property
 *
 * This property encodes the 'invite' property, as defined by
 * the 'caldav-sharing-02' spec, in the http://calendarserver.org/ns/
 * namespace.
 *
 * @see https://trac.calendarserver.org/browser/CalendarServer/trunk/doc/Extensions/caldav-sharing-02.txt
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Invite implements XmlSerializable {

	/**
	 * Creates the property.
	 *
	 * Users is an array. Each element of the array has the following
	 * properties:
	 *
	 *   * href - Often a mailto: address
	 *   * commonName - Optional, for example a first and lastname for a user.
	 *   * status - One of the SharingPlugin::STATUS_* constants.
	 *   * readOnly - true or false
	 *   * summary - Optional, description of the share
	 *
	 * The organizer key is optional to specify. It's only useful when a
	 * 'sharee' requests the sharing information.
	 *
	 * The organizer may have the following properties:
	 *   * href - Often a mailto: address.
	 *   * commonName - Optional human-readable name.
	 *   * firstName - Optional first name.
	 *   * lastName - Optional last name.
	 *
	 * If you wonder why these two structures are so different, I guess a
	 * valid answer is that the current spec is still a draft.
	 *
	 * @param array $users
	 */
	public function __construct(
		/**
		 * The list of users a calendar has been shared to.
		 */
		protected array $users,
		/**
		 * The organizer contains information about the person who shared the
		 * object.
		 */
		protected ?array $organizer = null,
	) {
	}

	/**
	 * Returns the list of users, as it was passed to the constructor.
	 *
	 * @return array
	 */
	public function getValue() {
		return $this->users;
	}

	/**
	 * The xmlSerialize method is called during xml writing.
	 *
	 * Use the $writer argument to write its own xml serialization.
	 *
	 * An important note: do _not_ create a parent element. Any element
	 * implementing XmlSerializble should only ever write what's considered
	 * its 'inner xml'.
	 *
	 * The parent of the current element is responsible for writing a
	 * containing element.
	 *
	 * This allows serializers to be re-used for different element names.
	 *
	 * If you are opening new elements, you must also close them again.
	 *
	 * @param Writer $writer
	 * @return void
	 */
	public function xmlSerialize(Writer $writer) {
		$cs = '{' . Plugin::NS_OWNCLOUD . '}';

		if (!is_null($this->organizer)) {
			$writer->startElement($cs . 'organizer');
			$writer->writeElement('{DAV:}href', $this->organizer['href']);

			if (isset($this->organizer['commonName']) && $this->organizer['commonName']) {
				$writer->writeElement($cs . 'common-name', $this->organizer['commonName']);
			}
			if (isset($this->organizer['firstName']) && $this->organizer['firstName']) {
				$writer->writeElement($cs . 'first-name', $this->organizer['firstName']);
			}
			if (isset($this->organizer['lastName']) && $this->organizer['lastName']) {
				$writer->writeElement($cs . 'last-name', $this->organizer['lastName']);
			}
			$writer->endElement(); // organizer
		}

		foreach ($this->users as $user) {
			$writer->startElement($cs . 'user');
			$writer->writeElement('{DAV:}href', $user['href']);
			if (isset($user['commonName']) && $user['commonName']) {
				$writer->writeElement($cs . 'common-name', $user['commonName']);
			}
			$writer->writeElement($cs . 'invite-accepted');

			$writer->startElement($cs . 'access');
			if ($user['readOnly']) {
				$writer->writeElement($cs . 'read');
			} else {
				$writer->writeElement($cs . 'read-write');
			}
			$writer->endElement(); // access

			if (isset($user['summary']) && $user['summary']) {
				$writer->writeElement($cs . 'summary', $user['summary']);
			}

			$writer->endElement(); //user
		}
	}
}
