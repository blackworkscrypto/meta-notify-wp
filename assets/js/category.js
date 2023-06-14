window.addEventListener("DOMContentLoaded", () => {
  const deleteButtons = document.querySelectorAll(
    ".meta-notify-category-delete-btn"
  );
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const categoryId = e.currentTarget.dataset.categoryId;
      console.log(categoryId);
      // here you can do your delete logic by sending the categoryId to the server

      console.log("clicked delete category");

      if (confirm("Are you sure you want to delete?")) {
        fetch(ajaxurl, {
          method: "POST",
          body: new URLSearchParams({
            categoryId: e.currentTarget.dataset.categoryId,
            plugin: e.currentTarget.dataset.plugin,
            action: "metanotify_delete_category",
          }),
        })
          .then((res) => {
            console.log(res);
            return res.json();
          })
          .then((result) => {
            if (result.success) {
              alert("Category deleted");
             
              window.location.reload();
            }
            else {
              alert("Category deletion failed.!");
            }
            console.log(result);
          })
          .catch((err) => {
            console.log(err);
            alert("An error occurred while deleting Category.");
          });
      }
    });
  });

  const categoryAddBtn = document.getElementById(
    "meta-notify-category-add-btn"
  );
  if (categoryAddBtn) {
    categoryAddBtn.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("clicked addd category");
      const categoryName = document.getElementById(
        "meta-notify-category-name"
      ).value;
      console.log(categoryName);
      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams({
          categoryName: categoryName,
          plugin: e.currentTarget.dataset.plugin,
          action: "metanotify_add_category",
        }),
      })
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          console.log(result);
          if (result.name) {
            alert("Category added successfully: ");
            window.location.reload();
          
          }
          else {
            alert("Error adding Category: " + result.notificationStatus);
          }
          
        })
        .catch((err) => {
          console.log(err);
        });
    });
  }
  const categorySearchBtn = document.getElementById(
    "meta-notify-category-search-btn"
  );
  if (categorySearchBtn) {
    categorySearchBtn.addEventListener("click", (e) => {
      e.preventDefault();
      console.log("clicked search category");
      const categoryId = document.getElementById(
        "meta-notify-category-id"
      ).value;
      console.log(categoryId);
      fetch(
        ajaxurl +
          "?categoryId=" +
          categoryId +
          "&plugin=" +
          e.currentTarget.dataset.plugin +
          "&action=metanotify_search_category",
        {
          method: "GET",
        }
      )
        .then((res) => {
          return res.json();
        })
        .then((result) => {
          if (result.name) {
            document.getElementById("meta-notify-category-details").value =
              result.name;
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
