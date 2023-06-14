window.addEventListener("DOMContentLoaded", () => {
  const notificationAddBtn = document.getElementById(
    "meta-notify-notification-add-btn"
  );

  if (notificationAddBtn) {
    notificationAddBtn.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("clicked addd notification");

      const notificationTitle = document.getElementById(
        "meta-notify-notification-title"
      ).value;
      const notificationBody = document.getElementById(
        "meta-notify-notification-body"
      ).value;
      const notificationImage = document.getElementById(
        "meta-notify-notification-image"
      ).value;
      const selectCategory = document.getElementById(
        "meta-notify-choosen-category"
      );
      const selectedCategories = selectCategory.selectedOptions;
      const notificationCategory = [];

      for (let i = 0; i < selectedCategories.length; i++) {
        notificationCategory.push(selectedCategories[i].value);
      }
      console.log(notificationCategory);
      const selectElement = document.getElementById(
        "meta-notify-choosen-visitors"
      );
      const selectedOptions = selectElement.selectedOptions;
      const notificationVisitors = [];

      if (selectedOptions.length > 0) {
        for (let i = 0; i < selectedOptions.length; i++) {
          notificationVisitors.push(selectedOptions[i].value);
        }
      }

      console.log(notificationVisitors);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams({
          notificationTitle: notificationTitle,
          notificationBody: notificationBody,
          notificationImage: notificationImage,
          notificationCategory: notificationCategory,
          notificationVisitors: notificationVisitors,
          plugin: e.currentTarget.dataset.plugin,
          action: "metanotify_add_notification",
        }),
      })
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          console.log(result);
          console.log(result.notificationStatus);
          console.log(result.notificationMessage);
          if (result.notificationStatus) {
            alert("Notification added successfully: ");
            window.location.reload();
          } else {
            alert(
              "Error adding notification. Enter all details and try again "
            );
          }
        })
        .catch((err) => {
          console.log(err);
          alert("An error occurred while adding the notification.");
        });
    });
  }

  const notificationSearchBtn = document.getElementById(
    "meta-notify-notification-search-btn"
  );
  if (notificationSearchBtn) {
    notificationSearchBtn.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("clicked search notification");
      const notificationId = document.getElementById(
        "meta-notify-notification-id"
      ).value;
      console.log(notificationId);
      fetch(
        ajaxurl +
          "?notificationId=" +
          notificationId +
          "&plugin=" +
          e.currentTarget.dataset.plugin +
          "&action=metanotify_search_notification",
        {
          method: "GET",
        }
      )
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          if (result.notificationStatus) {
            document.getElementById("meta-notify-notification-details").value =
              result.notificationStatus;
            // window.location.reload();
          }
          console.log(result);
        })
        .catch((err) => {
          console.log(err);
        });
    });
  }
});
