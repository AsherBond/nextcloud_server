/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { describe, expect, test } from 'vitest'

import {
	ATOMIC_PERMISSIONS,
	BUNDLED_PERMISSIONS,
	addPermissions,
	subtractPermissions,
	hasPermissions,
	permissionsSetIsValid,
	togglePermissions,
	canTogglePermissions,
} from '../lib/SharePermissionsToolBox.js'

describe('SharePermissionsToolBox', () => {
	test('Adding permissions', () => {
		expect(addPermissions(ATOMIC_PERMISSIONS.NONE, ATOMIC_PERMISSIONS.NONE)).toBe(ATOMIC_PERMISSIONS.NONE)
		expect(addPermissions(ATOMIC_PERMISSIONS.NONE, ATOMIC_PERMISSIONS.READ)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(addPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.READ)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(addPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.UPDATE)).toBe(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE)
		expect(addPermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE | ATOMIC_PERMISSIONS.SHARE)).toBe(BUNDLED_PERMISSIONS.ALL)
		expect(addPermissions(BUNDLED_PERMISSIONS.ALL, ATOMIC_PERMISSIONS.READ)).toBe(BUNDLED_PERMISSIONS.ALL)
		expect(addPermissions(BUNDLED_PERMISSIONS.ALL, ATOMIC_PERMISSIONS.NONE)).toBe(BUNDLED_PERMISSIONS.ALL)
	})

	test('Subtract permissions', () => {
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.NONE)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.READ)).toBe(ATOMIC_PERMISSIONS.NONE)
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.UPDATE)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.UPDATE)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE)).toBe(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE)
		expect(subtractPermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.UPDATE | ATOMIC_PERMISSIONS.DELETE)).toBe(ATOMIC_PERMISSIONS.READ)
		expect(subtractPermissions(BUNDLED_PERMISSIONS.ALL, ATOMIC_PERMISSIONS.READ)).toBe(ATOMIC_PERMISSIONS.UPDATE | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE | ATOMIC_PERMISSIONS.SHARE)
	})

	test('Has permissions', () => {
		expect(hasPermissions(ATOMIC_PERMISSIONS.NONE, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(hasPermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.NONE)).toBe(true)
		expect(hasPermissions(BUNDLED_PERMISSIONS.READ_ONLY, ATOMIC_PERMISSIONS.READ)).toBe(true)
		expect(hasPermissions(BUNDLED_PERMISSIONS.READ_ONLY, ATOMIC_PERMISSIONS.UPDATE)).toBe(false)
		expect(hasPermissions(BUNDLED_PERMISSIONS.READ_ONLY, ATOMIC_PERMISSIONS.DELETE)).toBe(false)
		expect(hasPermissions(BUNDLED_PERMISSIONS.ALL, ATOMIC_PERMISSIONS.DELETE)).toBe(true)
	})

	test('Toggle permissions', () => {
		expect(togglePermissions(BUNDLED_PERMISSIONS.ALL, BUNDLED_PERMISSIONS.UPLOAD_AND_UPDATE)).toBe(ATOMIC_PERMISSIONS.SHARE)
		expect(togglePermissions(BUNDLED_PERMISSIONS.ALL, BUNDLED_PERMISSIONS.FILE_DROP)).toBe(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE | ATOMIC_PERMISSIONS.DELETE | ATOMIC_PERMISSIONS.SHARE)
		expect(togglePermissions(BUNDLED_PERMISSIONS.ALL, ATOMIC_PERMISSIONS.NONE)).toBe(BUNDLED_PERMISSIONS.ALL)
		expect(togglePermissions(ATOMIC_PERMISSIONS.NONE, BUNDLED_PERMISSIONS.ALL)).toBe(BUNDLED_PERMISSIONS.ALL)
		expect(togglePermissions(ATOMIC_PERMISSIONS.READ, BUNDLED_PERMISSIONS.ALL)).toBe(BUNDLED_PERMISSIONS.ALL)
	})

	test('Permissions set is valid', () => {
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.NONE)).toBe(false)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.READ)).toBe(true)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.CREATE)).toBe(true)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.UPDATE)).toBe(false)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.DELETE)).toBe(false)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE)).toBe(true)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.DELETE)).toBe(true)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.UPDATE)).toBe(false)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE)).toBe(false)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.UPDATE)).toBe(true)
		expect(permissionsSetIsValid(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE)).toBe(true)
	})

	test('Toggle permissions', () => {
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.CREATE, ATOMIC_PERMISSIONS.READ)).toBe(true)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.DELETE, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE, ATOMIC_PERMISSIONS.READ)).toBe(false)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.UPDATE, ATOMIC_PERMISSIONS.CREATE)).toBe(true)
		expect(canTogglePermissions(ATOMIC_PERMISSIONS.READ | ATOMIC_PERMISSIONS.CREATE | ATOMIC_PERMISSIONS.DELETE, ATOMIC_PERMISSIONS.CREATE)).toBe(true)
	})
})
