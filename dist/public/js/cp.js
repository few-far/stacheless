(() => {
  // src/Cms/Redirects/resources/js/RedirectListing.js
  var RedirectListing_default = {
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
      columns: []
    },
    data() {
      return {
        sortColumn: null,
        sortDirection: null,
        meta: null,
        searchQuery: null
      };
    },
    methods: {
      sorted(column, direction) {
        this.sortColumn = column;
        this.sortDirection = direction;
      }
    }
  };

  // src/Cms/Redirects/resources/js/PublishForm.js
  var PublishForm_default = {
    template: `
		<publish-form v-bind="form" @saved="saved" />
	`,
    props: {
      form: Object
    },
    methods: {
      saved(response) {
        const redirect = lodash.get(response, "data.redirect");
        if (redirect) {
          window.location = redirect;
        }
      }
    }
  };

  // src/Cms/Forms/resources/js/CmsFormSubmissionsFieldtype.js
  var CmsFormSubmissionsFieldtype_default = {
    mixins: [window.Fieldtype],
    template: `
		<div class="grid text-sm">
			<div v-if="!meta.action" class="help-block">
				Form submission by users will appear here.
			</div>

			<div v-else-if="!page" class="help-block">Loading...</div>

			<div v-else>
				<table class="w-full">
					<template v-for="submission in page.items">
						<tr :key="\`\${ submission.id }-row\`" :data-id="submission.id">
							<td :title="submission.id" class="pr-1" v-text="submission.payload ? submission.payload.form_title : null" />
							<td v-text="submission.created_at" />
							<td v-text="submission.first_email" />
							<td class="text-right">
								<button @click.prevent="$set(open, submission.id, !open[submission.id])" class="text-blue hover:text-black">
									{{ open[submission.id] ? 'Hide details' : 'Show details' }}
								</button>
							</td>
						</tr>

						<tr v-if="open[submission.id]" :key="\`\${ submission.id }-details\`" :data-id="submission.id">
							<td colspan="4">
								<div class="m-1 p-2 bg-grey-10 rounded-md">
									<strong>Submission</strong>
									<dl class="grid grid-cols-2 gap-.5">
										<template v-for="(row, n) in submission.payload.field_rows">
											<dt :key="\`\${ n }-key\`">{{ row.label }} ({{ row.name }})</dt>
											<dd :key="\`\${ n }-value\`">{{ row.value }}</dd>
										</template>
									</dl>

									<br>

									<strong>Details</strong>
									<dl class="grid grid-cols-2 gap-.5">
										<template v-for="(row, n) in submission.payload.meta_rows">
											<dt :key="\`\${ n }-key\`">{{ row.name }}</dt>
											<dd :key="\`\${ n }-value\`">{{ row.value }}</dd>
										</template>
									</dl>
								</div>
							</td>
						</tr>
					</template>
				</table>

				<ul v-if="page.pagination" class="max-w-container mx-auto flex justify-center mt-1 gap-.5">
					<li
						v-for="(url, n) in page.pagination.window.first"
						:key="\`first-\${ n }\`"
					>
						<a
							class="underline transition-colors hover:decoration-current"
							:class="page.pagination.current === n ? 'decoration-current' : 'decoration-transparent'"
							v-text="n"
							@click.prevent="load(url)"
						/>
					</li>

					<li v-if="page.pagination.window.first && (page.pagination.window.slider || page.pagination.window.last)">\u2022</li>

					<li
						v-for="(url, n) in page.pagination.window.slider"
						:key="\`slider-\${ n }\`"
					>
						<a
							class="underline transition-colors hover:decoration-current"
							:class="page.pagination.current === n ? 'decoration-current' : 'decoration-transparent'"
							v-text="n"
							@click.prevent="load(url)"
						/>
					</li>

					<li v-if="page.pagination.window.last && (page.pagination.window.slider || page.pagination.window.first)">\u2022</li>

					<li
						v-for="(url, n) in page.pagination.window.last"
						:key="\`last-\${ n }\`"
					>
						<button
							class="underline transition-colors hover:decoration-current"
							:class="page.pagination.current === n ? 'decoration-current' : 'decoration-transparent'"
							v-text="n"
							@click.prevent="load(url)"
						/>
					</li>
				</ul>
			</div>
		</div>
	`,
    data() {
      return {
        page: null,
        open: {}
      };
    },
    computed: {},
    mounted() {
      if (this.meta.action) {
        this.load(this.meta.action);
      }
    },
    methods: {
      load(url) {
        axios.get(url).then((response) => {
          this.page = response.data;
        });
      }
    }
  };

  // src/Cms/cp.js
  var module = {
    components: {
      RedirectListing: RedirectListing_default,
      RedirectPublishForm: PublishForm_default,
      CmsFormSubmissionsFieldtype: CmsFormSubmissionsFieldtype_default
    },
    registerFormsModule() {
      Statamic.$components.register("cms_form_submissions-fieldtype", CmsFormSubmissionsFieldtype_default);
    },
    registerRedirectModule() {
      Statamic.$components.register("redirect-listing", RedirectListing_default);
      Statamic.$components.register("redirect-publish-form", PublishForm_default);
    },
    registerShortcutPublish() {
      Statamic.$keys.bindGlobal("command+shift+s", () => {
        const publish = document.querySelector(".workspace .page-wrapper .breadcrumb + .flex button.btn-primary");
        if (!publish || lodash.get(publish.firstElementChild, true)) {
          return;
        }
        publish.click();
        window.setTimeout(() => {
          const publish2 = document.querySelector(".vue-portal-target.stack .btn-primary");
          if (!publish2) {
            return;
          }
          publish2.focus();
        }, 0);
      });
    },
    defaultOptions: {
      "registerRedirectModule": true,
      "registerShortcutPublish": true,
      "registerFormsModule": true
    },
    register(options) {
      const opts = Object.assign({}, module.defaultOptions, options);
      if (opts.registerRedirectModule) {
        module.registerRedirectModule();
      }
      if (opts.registerShortcutPublish) {
        module.registerShortcutPublish();
      }
      if (opts.registerFormsModule) {
        module.registerFormsModule();
      }
    }
  };
  window.Stacheless = module;
})();
