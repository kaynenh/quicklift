(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    window.AcquiaLift = {
        account_id: quicklift.account_id,
        liftAssetsURL: quicklift.assets,
        liftDecisionAPIURL: quicklift.decision_api,
        authEndpoint: quicklift.auth_endpoint,
        contentReplacementMode: "trusted",
        site_id: quicklift.site_id,
        profile: {
            'author':quicklift.author,
            'engagement_score':quicklift.engagement_score,
            'page_type':quicklift.page_type,
            'post_id':quicklift.post_id,
            'published_date':quicklift.published_date,
            'content_title':quicklift.content_title,
            'content_type':quicklift.content_type,
            'content_section':quicklift.content_section,
            'content_keywords':quicklift.content_keywords,
            'persona':quicklift.persona
        }
    };

})( jQuery );
