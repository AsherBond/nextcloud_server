/**
 * @copyright Copyright (c) 2023 Ferdinand Thiessen <opensource@fthiessen.de>
 *
 * @author Ferdinand Thiessen <opensource@fthiessen.de>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import { getRowForFile, triggerActionForFile } from './FilesUtils.ts'

describe('Files: Move or copy files', { testIsolation: true }, () => {
	let currentUser
	beforeEach(() => {
		cy.createRandomUser().then((user) => {
			currentUser = user
			cy.login(user)
		})
	})
	afterEach(() => {
		// nice to have cleanup
		cy.deleteUser(currentUser)
	})


	it('Can copy a file to new folder', () => {
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
			.mkdir(currentUser, '/new-folder')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the move so we can wait for it
		cy.intercept('COPY', /\/remote.php\/dav\/files\//).as('copyFile')

		getRowForFile('original.txt').should('be.visible')
		triggerActionForFile('original.txt', 'move-copy')

		// select new folder
		cy.get('.file-picker [data-filename="new-folder"]').should('be.visible').click()
		// click copy
		cy.get('.file-picker').contains('button', 'Copy to new-folder').should('be.visible').click()

		// wait for copy to finish
		cy.wait('@copyFile')

		getRowForFile('new-folder').find('[data-cy-files-list-row-name-link]').click()

		cy.url().should('contain', 'dir=/new-folder')
		getRowForFile('original.txt').should('be.visible')
		getRowForFile('new-folder').should('not.exist')
	})

	it('Can move a file to new folder', () => {
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
			.mkdir(currentUser, '/new-folder')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the move so we can wait for it
		cy.intercept('MOVE', /\/remote.php\/dav\/files\//).as('moveFile')

		getRowForFile('original.txt').should('be.visible')
		triggerActionForFile('original.txt', 'move-copy')

		// select new folder
		cy.get('.file-picker [data-filename="new-folder"]').should('be.visible').click()
		// click copy
		cy.get('.file-picker').contains('button', 'Move to new-folder').should('be.visible').click()

		// wait for move to finish
		cy.wait('@moveFile')

		// wait until visible again
		getRowForFile('new-folder').should('be.visible')
		// original should be moved -> not exist anymore
		getRowForFile('original.txt').should('not.exist')
		getRowForFile('new-folder').should('be.visible').find('[data-cy-files-list-row-name-link]').click()

		cy.url().should('contain', 'dir=/new-folder')
		getRowForFile('original.txt').should('be.visible')
		getRowForFile('new-folder').should('not.exist')
	})

	// This was a bug previously
	it('Can move a file to folder with similar name', () => {
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original')
			.mkdir(currentUser, '/original folder')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the move so we can wait for it
		cy.intercept('MOVE', /\/remote.php\/dav\/files\//).as('moveFile')

		getRowForFile('original').should('be.visible')
		triggerActionForFile('original', 'move-copy')

		// select new folder
		cy.get('.file-picker [data-filename="original folder"]').should('be.visible').click()
		// click copy
		cy.get('.file-picker').contains('button', 'Move to original folder').should('be.visible').click()

		cy.wait('@moveFile')
		// wait until visible again
		getRowForFile('original folder').should('be.visible')

		// original should be moved -> not exist anymore
		getRowForFile('original').should('not.exist')
		getRowForFile('original folder').should('be.visible').find('[data-cy-files-list-row-name-link]').click()

		cy.url().should('contain', 'dir=/original%20folder')
		getRowForFile('original').should('be.visible')
		getRowForFile('original folder').should('not.exist')
	})

	it('Can move a file to its parent folder', () => {
		cy.mkdir(currentUser, '/new-folder')
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/new-folder/original.txt')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the move so we can wait for it
		cy.intercept('MOVE', /\/remote.php\/dav\/files\//).as('moveFile')

		getRowForFile('new-folder').should('be.visible').find('[data-cy-files-list-row-name-link]').click()
		cy.url().should('contain', 'dir=/new-folder')

		getRowForFile('original.txt').should('be.visible')
		triggerActionForFile('original.txt', 'move-copy')

		// select new folder
		cy.get('.file-picker button[title="Home"]').should('be.visible').click()
		// click move
		cy.get('.file-picker').contains('button', 'Move').should('be.visible').click()

		// wait for move to finish
		cy.wait('@moveFile')

		// wait until visible again
		cy.get('main').contains('No files in here').should('be.visible')
		// original should be moved -> not exist anymore
		getRowForFile('original.txt').should('not.exist')

		cy.visit('/apps/files')
		getRowForFile('new-folder').should('be.visible')
		getRowForFile('original.txt').should('be.visible')
	})

	it('Can copy a file to same folder', () => {
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the copy so we can wait for it
		cy.intercept('COPY', /\/remote.php\/dav\/files\//).as('copyFile')

		getRowForFile('original.txt').should('be.visible')
		triggerActionForFile('original.txt', 'move-copy')

		// click copy
		cy.get('.file-picker').contains('button', 'Copy').should('be.visible').click()

		cy.wait('@copyFile')
		getRowForFile('original.txt').should('be.visible')
		getRowForFile('original (copy).txt').should('be.visible')
	})

	it('Can copy a file multiple times to same folder', () => {
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
		cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original (copy).txt')
		cy.login(currentUser)
		cy.visit('/apps/files')

		// intercept the copy so we can wait for it
		cy.intercept('COPY', /\/remote.php\/dav\/files\//).as('copyFile')

		getRowForFile('original.txt').should('be.visible')
		triggerActionForFile('original.txt', 'move-copy')

		// click copy
		cy.get('.file-picker').contains('button', 'Copy').should('be.visible').click()

		cy.wait('@copyFile')
		getRowForFile('original.txt').should('be.visible')
		getRowForFile('original (copy 2).txt').should('be.visible')
	})

	/** Test for https://github.com/nextcloud/server/issues/43329 */
	context.only('escaping file and folder names', () => {
		it('Can handle files with special characters', () => {
			cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
				.mkdir(currentUser, '/can\'t say')
			cy.login(currentUser)
			cy.visit('/apps/files')

			copyFile('original.txt', 'can\'t say')

			navigateToFolder('can\'t say')

			cy.url().should('contain', 'dir=/can%27t%20say')
			getRowForFile('original.txt').should('be.visible')
			getRowForFile('can\'t say').should('not.exist')
		})

		/**
		 * If escape is set to false (required for test above) then "<a>foo" would result in "<a>foo</a>" if sanitizing is not disabled
		 * We should disable it as vue already escapes the text when using v-text
		 */
		it('does not incorrectly sanitize file names', () => {
			cy.uploadContent(currentUser, new Blob(), 'text/plain', '/original.txt')
				.mkdir(currentUser, '/<a href="#">foo')
			cy.login(currentUser)
			cy.visit('/apps/files')

			copyFile('original.txt', '<a href="#">foo')

			navigateToFolder('<a href="#">foo')

			cy.url().should('contain', 'dir=/%3Ca%20href%3D%22%23%22%3Efoo')
			getRowForFile('original.txt').should('be.visible')
			getRowForFile('<a href="#">foo').should('not.exist')
		})
	})
})
