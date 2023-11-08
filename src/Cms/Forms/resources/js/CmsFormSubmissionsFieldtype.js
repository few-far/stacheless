export default {
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

					<li v-if="page.pagination.window.first && (page.pagination.window.slider || page.pagination.window.last)">•</li>

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

					<li v-if="page.pagination.window.last && (page.pagination.window.slider || page.pagination.window.first)">•</li>

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
			open: {},
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
			axios.get(url).then(response => {
				this.page = response.data;
			});
		},
	},
};
