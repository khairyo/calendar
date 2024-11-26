<!--
  - SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<FreeBusy v-if="initialized"
		:dialog-name="dialogName"
		:start-date="startDate"
		:end-date="endDate"
		:organizer="organizer"
		:attendees="attendees"
		@close="close"
	/>
</template>

<script>
import { mapStores } from 'pinia'
import usePrincipalsStore from '../store/principals.js'
import {
	mapAttendeePropertyToAttendeeObject,
	mapPrincipalObjectToAttendeeObject,
} from '../models/attendee.js'
import { initializeClientForUserView } from '../services/caldavService.js'
import FreeBusy from '../components/Editor/FreeBusy/FreeBusy.vue'
import { AttendeeProperty } from '@nextcloud/calendar-js'

export default {
	name: 'ContactsMenuAvailability',
	components: {
		FreeBusy,
	},
	props: {
		userId: {
			type: String,
			required: true,
		},
		userDisplayName: {
			type: String,
			required: true,
		},
		userEmail: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			initialized: false,
		}
	},
	computed: {
		...mapStores(usePrincipalsStore),
		dialogName() {
			return t('calendar', 'Availability of {displayName}', {
				displayName: this.userDisplayName,
			})
		},
		startDate() {
			return new Date()
		},
		endDate() {
			const date = this.startDate
			date.setSeconds(0)
			date.setMinutes(0)
			date.setHours(0)
			date.setDate(date.getDate() + 1)
			return date
		},
		organizer() {
			if (!this.principalsStore.getCurrentUserPrincipal) {
				throw new Error('No principal available for current user')
			}

			return mapPrincipalObjectToAttendeeObject(
				this.principalsStore.getCurrentUserPrincipal,
				true,
			)
		},
		attendees() {
			const attendee = AttendeeProperty.fromNameAndEMail(this.userId, this.userEmail, false)
			return [mapAttendeePropertyToAttendeeObject(attendee)]
		},
	},
	async created() {
		// Initialize CalDAV service and fetch current user principal
		await initializeClientForUserView()
		await this.principalsStore.fetchCurrentUserPrincipal()
		this.initialized = true
	},
	methods: {
		close() {
			this.$destroy();
		},
	},
}
</script>

<style lang="scss" scoped>
</style>
