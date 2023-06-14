import Pickr from "@simonwep/pickr";

(function () {
  /**
   * Handle image upload
   */
  function uploadImage() {
    if (this.files && this.files[0]) {
      let img = this.previousElementSibling.querySelector("img"),
        reader = new FileReader();
      reader.onload = (e) => {
        this.nextElementSibling.value = e.target.result;
        if (img) {
          img.src = e.target.result;
        }
      };
      reader.readAsDataURL(this.files[0]);
    }
  }

  /**
   *
   * @param {Object} instance An instance of Pickr
   * @param {string} color Picked color
   */
  function applyPickrColor(instance, color) {
    const button = instance._root.button;

    if (
      !button ||
      !button.parentElement ||
      !button.parentElement.classList.contains("pickr")
    ) {
      return;
    }

    button.style = "--pcr-color:" + color;

    button.parentElement.nextElementSibling.value = color;
  }

  /**
   * @see https://github.com/Simonwep/pickr
   * @param {string} el Element className
   * @returns
   */
  function dispatchPickr(el) {
    const _el = document.querySelector(el);

    if (!_el || !_el.dataset.owner) {
      return;
    }

    const owner = document.getElementById(_el.dataset.owner);

    if (!owner) {
      return;
    }

    const defaultColor = owner.value;

    const pickr = new Pickr({
      el: el,
      theme: "classic",
      default: defaultColor,
      components: {
        preview: true,
        opacity: true,
        hue: true,
        interaction: {
          hex: true,
          rgba: true,
          input: true,
          save: false,
        },
      },
      closeOnScroll: true,
      outputPrecision: true,
      defaultRepresentation: "HEX",
    });

    pickr
      .on("change", (color, source, instance) => {
        if (!color) return;
        applyPickrColor(instance, color.toRGBA().toString(0));
      })
      .on("cancel", (instance) => {
        applyPickrColor(instance, defaultColor);
      });
  }

  window.addEventListener("DOMContentLoaded", () => {
    const uploadImg = document.querySelectorAll(".metanotifyUploadImg");

    if (uploadImg.length) {
      uploadImg.forEach((el) => el.addEventListener("change", uploadImage));
    }

    dispatchPickr(".meta-notify-pick-bg-color");
    dispatchPickr(".meta-notify-pick-input-color");
    dispatchPickr(".meta-notify-pick-button-color");
    dispatchPickr(".meta-notify-pick-message-color");
    dispatchPickr(".meta-notify-pick-input-bg-color");
    dispatchPickr(".meta-notify-pick-button-bg-color");
    dispatchPickr(".meta-notify-pick-button-hover-color");
    dispatchPickr(".meta-notify-pick-button-bg-hover-color"); 
    dispatchPickr(".meta-notify-pick-bg-color-2");
    dispatchPickr(".meta-notify-pick-input-color-2");
    dispatchPickr(".meta-notify-pick-button-color-2");
    dispatchPickr(".meta-notify-pick-message-color-2");
    dispatchPickr(".meta-notify-pick-input-bg-color-2");
    dispatchPickr(".meta-notify-pick-button-bg-color-2");
    dispatchPickr(".meta-notify-pick-button-hover-color-2");
    dispatchPickr(".meta-notify-pick-button-bg-hover-color-2");
  });
})();
