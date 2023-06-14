import * as PushAPI from "@pushprotocol/restapi";
const ethers = require("ethers");

/**
 * Metanotify
 */

class Metanotify {
  constructor() {
    const button = document.querySelector(".metanotifyConnect");
    const closeButton = document.getElementById("meta-notify-close-popup");
    var metaNoThanksButton = document.getElementById("metaNoThanks");
    if (metaNoThanksButton) {
      var metaNotifyDiv = document.getElementById("metaNotifyTextbox");
      metaNoThanksButton.addEventListener("click", function () {
        metaNotifyDiv.style.display = "none";
      });
    }

    if (button) {
      this.button = button;
      button.addEventListener("click", this.metanotifyShowPopup.bind(this));
    }
    if (closeButton) {
      closeButton.addEventListener("click", (e) => {
        button.removeAttribute("disabled");
        document.body.classList.remove("meta-notify-showing");
      });
    }
    const web3Buttons = document.querySelectorAll(
      ".metanotifyConnectWalletBtn"
    );

    if (web3Buttons) {
      web3Buttons.forEach((el) =>
        el.addEventListener("click", this.metanotifyConnectWallet.bind(this))
      );
    } else {
      console.log("No web3 connect button found!");
    }
  }

  notify(message, type = false) {
    if (!this.button) {
      return;
    }

    const heading = document.getElementById("meta-notify-box-message");
    if (type && !heading.classList.contains(type)) {
      heading.className = type;
    }
    if (message && typeof message === "string") {
      heading.innerText = message;
    } else {
      heading.innerText = metanotify.settings.message;
    }

    this.button.removeAttribute("disabled");
  }

  metanotifyPopupNotify(message, type = false) {
    const notice = document.getElementById("metanotify-popup-notice");

    if (notice) {
      if (type && !notice.classList.contains(type)) {
        notice.className = type;
      }
      notice.innerHTML = message;
    }
  }

  metanotifyShowPopup() {
    this.button.setAttribute("disabled", true);

    const popupEl = document.getElementById("meta-notify-popup");

    if (!popupEl) {
      this.button.removeAttribute("disabled");
      return;
    }
    const metanotifyCheck = document.getElementById("metaNotifyAgree");
    if (!metanotifyCheck.checked) {
      this.notify(
        "You must agree to our Privacy Policies!",
        "metanotifyMessageError"
      );
      return;
    }
    this.notify(
      "Sign up and get notified when we release new content by connecting your wallet.",
      "metanotifyMessage"
    );
    document.body.classList.add("meta-notify-showing");
  }

  async metanotifyConnectWallet(e) {
    this.metanotifyPopupNotify("Connecting your wallet...", "normal");

    const checkboxes = document.querySelectorAll(
      'input[name="meta-notify-notification-category-choosen[]"]:checked'
    );
    const notificationCategoryChoosen = [];

    for (var i = 0; i < checkboxes.length; i++) {
      notificationCategoryChoosen.push(checkboxes[i].value);
    }

    console.log(notificationCategoryChoosen);
    const el = e.target.closest(".metanotifyConnectWalletBtn");

    let wallet;

    try {
      wallet = await this.getWallet(el.dataset.wallet);
    } catch (error) {
      this.metanotifyPopupNotify(error.message, "red");
      return;
    }

    if (metanotify.settings.paid_mode) {
      const paid = await this.chargeUser(wallet, accounts[0]);
      if (!paid) {
        this.metanotifyPopupNotify(
          "Transaction failed. Please try again!",
          "red"
        );
        return;
      }
    }

    fetch(metanotify.ajaxURL, {
      method: "POST",
      body: new URLSearchParams({
        nonce: metanotify.nonce,
        action: "metanotify_unlock_user",
        link: window.location.href,
        notificationCategoryChoosen: notificationCategoryChoosen,

        address: wallet.address,
        balance: wallet.balance,
        walletType: el.dataset.wallet,
      }),
    })
      .then((res) => {
        return res.json();
      })

      .then(async (result) => {
        if (result.success) {
          this.metanotifyPopupNotify(
            "Wallet connected , sign in from the popup ",
            "yellow"
          );
          if (window.ethereum) {
            await window.ethereum.enable();
            const provider = new ethers.providers.Web3Provider(window.ethereum);
            const signer = provider.getSigner();

            const userAddress = "eip155:5:" + (await signer.getAddress());

            await PushAPI.channels.subscribe({
              signer: signer,
              channelAddress:
                "eip155:5:0xc57B94263A8166e9A4E7ea3C290e0c29173326c6",
              userAddress: userAddress, // user address in CAIP
              onSuccess: () => {
                this.metanotifyPopupNotify(result.message, "green");
                console.log("opted in successfully");
                setTimeout(() => window.location.reload(), 2000);
              },
              onError: (e) => {
                console.error(e);
                console.error("opted in error");
                this.metanotifyPopupNotify(
                  "sign in Error. Try again to optin to our channel.",
                  "red"
                );
               
              },
              env: "staging",
            });
          } else {
            this.metanotifyPopupNotify(
              "Could not find the metamask browser plugin. If you're on mobile, please use the metamask browser.",
              "red"
            );
          }
        } else {
          this.metanotifyPopupNotify(result.message);
        }
      })
      .catch((err) => {
        this.metanotifyPopupNotify(err.message);
      });
  }

  async chargeUser(wallet, address = false) {
    let paid = false;

    if (wallet.isPhantom && window.solanaWeb3 && wallet.publicKey) {
      const connection = new solanaWeb3.Connection(
          solanaWeb3.clusterApiUrl(metanotify.solanaCluster),
          "confirmed"
        ),
        transaction = new solanaWeb3.Transaction();
      transaction.add(
        solanaWeb3.SystemProgram.transfer({
          fromPubkey: wallet.publicKey,
          toPubkey: metanotify.settings.solana_receiver_wallet,
          lamports: metanotify.settings.solana_charge_amount,
        })
      );
      transaction.feePayer = wallet.publicKey;
      transaction.recentBlockhash = (
        await connection.getRecentBlockhash()
      ).blockhash;
      let result;
      try {
        const { signature } = await window.solana.signAndSendTransaction(
          transaction
        );
        result = await connection.confirmTransaction(signature);
      } catch (error) {
        this.metanotifyPopupNotify(error.message);
      }
      if (result.err) {
        this.metanotifyPopupNotify(result.err);
      } else {
        paid = true;
      }
    } else {
      if (!window.ethers) return false;
      wallet
        .request({
          method: "eth_sendTransaction",
          params: [
            {
              from: address,
              to: metanotify.settings.receiver_wallet,
              value: ethers.utils.parseEther(
                metanotify.settings.charge_amount.toString()
              )._hex,
            },
          ],
        })
        .then((txHash) => {
          paid = true;
        })
        .catch((error) => {
          this.metanotifyPopupNotify(error.message);
        });
    }

    return paid;
  }

  static isInfuraProjectId() {
    console.log("infura");
    console.log(metaNotify.settings.infura_project_id);
    console.log(metaNotify.settings.infura_project_id_2);
    if (
      metaNotify.settings.infura_project_id &&
      metaNotify.settings.infura_project_id !== "undefined" &&
      metaNotify.settings.infura_project_id !== null &&
      metaNotify.settings.infura_project_id !== ""
    ) {
      return true;
    } else {
      if (
        metaNotify.settings.infura_project_id_2 &&
        metaNotify.settings.infura_project_id_2 !== "undefined" &&
        metaNotify.settings.infura_project_id_2 !== null &&
        metaNotify.settings.infura_project_id_2 !== ""
      ) {
        return true;
      }
      return false;
    }
  }

  //if (window.innerWidth <= 500 && isInfuraProjectId()) {
  async getWallet(type) {
    if ("phantom" === type) {
      return this.getPhantomWallet();
    }

    const provider = this.getWalletProvider(type);
    if (!provider) {
      throw new Error(
        "The wallet extension is not installed.<br>Please install it to continue!",
        "red"
      );
    }
    if (
      "coinbase" != type &&
      ("wallet_connect" == type || this.GetWindowSize() == true)
    ) {
      await provider.enable();
    }

    var accounts = [];
    const ethProvider = new ethers.providers.Web3Provider(provider);
    try {
      accounts = await ethProvider.listAccounts();
      if (!accounts[0]) {
        await ethProvider
          .send("eth_requestAccounts", [])
          .then(function (account_list) {
            accounts = account_list;
          });
      }
      //accounts = await provider.request({ method: 'eth_requestAccounts' });
    } catch (error) {
      console.log(error);
      throw new Error("Failed to connect your wallet. Please try again!");
    }

    if (!window.ethers || !accounts[0]) {
      throw new Error("Unable to connect to blockchain network!");
    }

    const balance = ethers.utils.formatEther(
      await ethProvider.getBalance(accounts[0])
    );

    return {
      address: accounts[0],
      balance,
    };
  }

  async getPhantomWallet() {
    if (!window.solana) {
      throw new Error(
        "Phantom wallet is not installed.<br>Please install it to continue!"
      );
    }

    let resp, account;

    try {
      resp = await solana.connect();
      account = resp.publicKey.toString();
    } catch (err) {
      throw new Error("Failed to connect your wallet. Please try again!");
    }

    if (!window.solanaWeb3 || !account) {
      throw new Error("Unable to connect to blockchain network!");
    }

    const connection = new solanaWeb3.Connection(
      solanaWeb3.clusterApiUrl("mainnet-beta"),
      "confirmed"
    );
    const balance = await connection.getBalance(resp.publicKey);

    return {
      address: account,
      balance,
    };
  }

  getWalletProvider(walletType) {
    let provider = false;
    let EnableWconnect = this.GetWindowSize();
    switch (walletType) {
      case "coinbase":
        if (typeof ethereum !== "undefined" && ethereum.providers) {
          provider = ethereum.providers.find((p) => p.isCoinbaseWallet);
        } else {
          provider = window.ethereum ? ethereum : !1;
        }
        break;
      case "binance":
        if (EnableWconnect == true) {
          provider = this.GetWalletConnectObject();
        } else if (window.BinanceChain) {
          provider = window.BinanceChain;
        }
        break;
      case "wallet_connect":
        provider = this.GetWalletConnectObject();

        break;
      case "phantom":
        if (window.solana) {
          provider = window.solana;
        }
        break;
      default:
        if (EnableWconnect == true) {
          provider = this.GetWalletConnectObject();
        } else if (typeof ethereum !== "undefined" && ethereum.providers) {
          provider = ethereum.providers.find((p) => p.isMetaMask);
        } else {
          provider = window.ethereum ? ethereum : !1;
        }
        break;
    }

    return provider;
  }

  GetWindowSize() {
    if (window.innerWidth <= 500) {
      return true;
    } else {
      return false;
    }
  }
  GetWalletConnectObject() {
    return new WalletConnectProvider.default({
      infuraId: metanotify.settings.infura_project_id
        ? metanotify.settings.infura_project_id
        : metanotify.settings.infura_project_id_2,
      rpc: {
        56: "https://bsc-dataseed.binance.org",
        97: "https://data-seed-prebsc-1-s1.binance.org:8545",
        137: "https://polygon-rpc.com",
        43114: "https://api.avax.network/ext/bc/C/rpc",
      },
    });
  }

  validateEmail() {
    if (this.button.parentElement.classList.contains("hide-email")) {
      return "N/A";
    } else {
      const email = this.button.previousElementSibling.value || "";
      if (
        email.match(
          /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        )
      ) {
        return email;
      } else {
        return false;
      }
    }
  }
}

export default Metanotify;
