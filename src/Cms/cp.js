import { default as RedirectListing } from './Redirects/resources/js/RedirectListing.js';
import { default as RedirectPublishForm } from './Redirects/resources/js/PublishForm.js';
import { default as CmsFormSubmissionsFieldtype } from './Forms/resources/js/CmsFormSubmissionsFieldtype.js';

const module = {
    components: {
        RedirectListing,
        RedirectPublishForm,
        CmsFormSubmissionsFieldtype,
    },

    registerFormsModule() {
        Statamic.$components.register('cms_form_submissions-fieldtype', CmsFormSubmissionsFieldtype);
    },

    registerRedirectModule() {
        Statamic.$components.register('redirect-listing', RedirectListing);
        Statamic.$components.register('redirect-publish-form', RedirectPublishForm);
    },

    registerShortcutPublish() {
        Statamic.$keys.bindGlobal('command+shift+s', () => {
            const publish = document.querySelector('.workspace .page-wrapper .breadcrumb + .flex button.btn-primary');

            if (!publish || lodash.get(publish.firstElementChild, 'tagName' !== 'SPAN')) {
                return;
            }

            publish.click();

            window.setTimeout(() => {
                const publish = document.querySelector('.vue-portal-target.stack .btn-primary');

                if (!publish) {
                    return;
                }

                publish.focus();
            }, 0);
        });
    },

    defaultOptions: {
        'registerRedirectModule': true,
        'registerShortcutPublish': true,
        'registerFormsModule': true,
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
    },
};

window.Stacheless = module;
