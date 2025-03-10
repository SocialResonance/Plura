<template>
	<div>
		<NcAppNavigation>
			<template #list>
				<NcAppNavigationItem :name="t('plura', 'Dashboard')" :to="{name: 'dashboard'}" icon="icon-dashboard" />
				<NcAppNavigationItem :name="t('plura', 'Proposals')" :to="{name: 'proposals'}" icon="icon-projects" />
				<NcAppNavigationItem :name="t('plura', 'Credit History')"
					:to="{name: 'credits'}"
					icon="icon-category-app-integration"
					data-cy="credit-history-link" />
			</template>
			<template #footer>
				<div class="plura-credits-display">
					<h3>{{ t('plura', 'Your Credits') }}</h3>
					<div class="plura-credit-balance" data-cy="user-credit-balance">
						{{ formatNumber(userCredits) }}
					</div>
				</div>
			</template>
		</NcAppNavigation>

		<NcAppContent>
			<router-view />
		</NcAppContent>
	</div>
</template>

<script>
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		NcAppContent,
		NcAppNavigation,
		NcAppNavigationItem,
	},
	data() {
		return {
			userCredits: 0,
			matchingFundTotal: 0,
			loading: true,
			error: null,
		}
	},
	mounted() {
		// Get user credits from the data attribute if available
		const container = document.getElementById('plura')
		if (container) {
			const userCredits = container.dataset.userCredits
			const matchingFund = container.dataset.matchingFund

			if (userCredits) {
				this.userCredits = parseFloat(userCredits)
			}

			if (matchingFund) {
				this.matchingFundTotal = parseFloat(matchingFund)
			}
		}

		// Fetch credits from API
		this.fetchUserCredits()
	},
	methods: {
		formatNumber(number) {
			return new Intl.NumberFormat().format(number)
		},
		fetchUserCredits() {
			axios.get(generateUrl('/apps/plura/api/credits'))
				.then((response) => {
					this.userCredits = response.data.credit_amount
					this.loading = false
				})
				.catch((error) => {
					console.error('Error fetching user credits', error)
					this.error = t('plura', 'Failed to load credit data')
					this.loading = false
				})
		},
	},
}
</script>

<style scoped lang="scss">
.plura-credits-display {
	padding: 10px;
	border-top: 1px solid var(--color-border);
	text-align: center;

	h3 {
		margin-bottom: 5px;
		font-weight: bold;
	}

	.plura-credit-balance {
		font-size: 18px;
		font-weight: bold;
		color: var(--color-primary);
	}
}
</style>
