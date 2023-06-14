import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { PanelBody, CheckboxControl } from "@wordpress/components";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";

registerBlockType("meta-notify/callout", {
  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    return (
      <>
        <InspectorControls>
          <PanelBody title={__("Display Settings", "meta-notify")}>
            <CheckboxControl
              label={__("Hide the email field", "meta-notify")}
              checked={attributes.noEmail}
              onChange={(value) => setAttributes({ noEmail: value })}
            />
          </PanelBody>
        </InspectorControls>
        <div {...blockProps}>metanotify</div>
      </>
    );
  },

  save: ({ attributes }) => {
    let className = "meta-notify-callout";

    if (attributes.noEmail) {
      className += " hide-email";
    }

    return (
      <div className={className} aria-hidden="true">
        metanotify
      </div>
    );
  },
});
