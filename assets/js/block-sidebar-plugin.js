import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";
import { registerPlugin } from "@wordpress/plugins";
import { CheckboxControl } from "@wordpress/components";
import { dispatch, select } from "@wordpress/data";
import { PluginDocumentSettingPanel } from "@wordpress/edit-post";

const MetanotifySidebarPlugin = () => {
  const meta = select("core/editor").getEditedPostAttribute("meta");
  const [isChecked, setChecked] = useState(meta["metanotifyDisabled"]);

  return (
    <PluginDocumentSettingPanel
      name="metanotifyPanel"
      title={__("Metanotify Settings", "meta-notify")}
    >
      <CheckboxControl
        label={__("Disable Auto-Insert", "meta-notify")}
        help={__("Do not insert popup automatically.", "meta-notify")}
        checked={isChecked}
        onChange={(value) => {
          setChecked(value);
          dispatch("core/editor").editPost({
            meta: {
              metanotifyDisabled: value ? "1" : "",
            },
          });
        }}
      />
    </PluginDocumentSettingPanel>
  );
};

registerPlugin("meta-notify-settings", {
  icon: "visibility",
  render: MetanotifySidebarPlugin,
});
