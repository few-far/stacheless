export default {
    template: `
		<div>
			<header class="mb-3">

				<div class="flex items-center">
					<h1 class="flex-1">Redirects</h1>

					<a
						class="btn-primary"
						:href="create_url"
						v-text="__('Create Redirect')"
					/>
				</div>

			</header>

			<data-list
				:rows="items"
				:columns="columns"
				:sort="false"
				:sort-column="sortColumn"
				:sort-direction="sortDirection"
			>
				<div>
					<div class="card p-0 relative">
						<div v-show="items.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

						<data-list-table
							v-show="items.length"
							:sortable="true"
							:allow-column-picker="true"
							@sorted="sorted"
						>
							<template slot="cell-source" slot-scope="{ row: redirect }">
								<div class="flex items-center">
									<div class="little-dot mr-1" :class="redirect.enabled ? 'bg-green' : 'bg-grey-40'" />

									<span class="font-mono text-2xs">{{ redirect.source }}</span>
								</div>
							</template>

							<template slot="cell-target" slot-scope="{ row: redirect }">
								<span class="font-mono text-2xs">{{ redirect.target }}</span>
							</template>

							<template slot="actions" slot-scope="{ row: redirect, index }">
								<dropdown-list>
									<dropdown-item :text="__('Edit')" :redirect="redirect.edit_url" />
									<div class="divider" />
									<dropdown-item
										:text="__('Delete')"
										class="warning"
										@click="$refs['deleter_' + redirect.id].confirm()"
									>
										<resource-deleter
											:ref="'deleter_' + redirect.id"
											:resource="redirect"
											:resource-title="redirect.source"
											:reload="true"
										/>
									</dropdown-item>
								</dropdown-list>
							</template>
						</data-list-table>
					</div>
				</div>
			</data-list>

		</div>
	`,

    props: {
		create_url: String,
		items: Array,
		columns: [],
    },

    data() {
        return {
            sortColumn: null,
            sortDirection: null,
            meta: null,
			searchQuery: null,
        }
    },

    methods: {
        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },
    }

};
