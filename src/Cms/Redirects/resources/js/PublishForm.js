export default {
    template: `
		<publish-form v-bind="form" @saved="saved" />
	`,

    props: {
		form: Object,
    },

    methods: {
        saved(response) {
			const redirect = lodash.get(response, 'data.redirect');

			if (redirect) {
				window.location = redirect;
			}
		}
    }
};
