<script id="npuswc-template-popup-customer-purchased-token" type="text/template">
	<div id="npuswc-popup-customer-purchased-token-outer-wrapper-<%- tokenId %>" class="npuswc-popup-customer-purchased-token">
		<div id="customer-download-jwt-inner-wrapper-<%- tokenId %>" class="npuswc-popup-customer-purchased-token-inner-wrapper">
			<textarea id="npuswc-textarea-customer-purchased-token-<%- tokenId %>" class="npuswc-textarea-customer-purchased-token" disabled><%- token %></textarea>
			<div class="npuswc-copy-close-buttons">
				<a id="npuswc-copy-text-<%- tokenId %>" class="npuswc-button npuswc-copy-text" href="javascript: void(0);" data-token-id="<%- tokenId %>"><%- textCopy %></a>
				<a id="npuswc-close-popup-<%- tokenId %>" class="npuswc-button npuswc-close-popup" href="javascript: void(0);" data-token-id="<%- tokenId %>"><%- textClose %></a>
			</div>
		</div>
	</div>
</script>