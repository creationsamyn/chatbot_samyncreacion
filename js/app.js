(function(){
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  const messages = $("#messages");
  const form = $("#chat-form");
  const input = $("#chat-input");
  const langBtns = $$(".lang-btn");
  const chips = $$(".chip");

  let lang = localStorage.getItem("mybot_lang") || "es";
  setLang(lang);

  // Initial greetings
  addMsg(getText("welcome"), "bot");

  // Language switch
  langBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const l = btn.dataset.lang;
      setLang(l);
    });
  });

  // Chips shortcuts
  chips.forEach(ch => ch.addEventListener("click", () => {
    input.value = ch.dataset.ask;
    form.dispatchEvent(new Event("submit"));
  }));

  // Submit message
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const text = (input.value || "").trim();
    if(!text) return;
    addMsg(text, "user");
    input.value = "";
    const rm = addTyping();

    try {
      const res = await fetch("api.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({ message: text, lang })
      });
      const data = await res.json();
      rm();
      if(data && data.ok) addMsg(data.reply, "bot");
      else addMsg(getText("error"), "bot");
    } catch(err){
      rm();
      addMsg(getText("network"), "bot");
    }
  });

  // Helpers
  function addMsg(text, who="bot"){
    const row = document.createElement("div");
    row.className = "row " + who;
    const b = document.createElement("div");
    b.className = "bubble";
    b.textContent = text;
    row.appendChild(b);
    messages.appendChild(row);
    messages.scrollTop = messages.scrollHeight;
  }
  function addTyping(){
    const row = document.createElement("div");
    row.className = "row typing";
    const b = document.createElement("div");
    b.className = "bubble";
    b.textContent = getText("typing");
    row.appendChild(b);
    messages.appendChild(row);
    messages.scrollTop = messages.scrollHeight;
    return ()=> row.remove();
  }
  function setLang(l){
    lang = (l === "fr") ? "fr" : "es";
    localStorage.setItem("mybot_lang", lang);
    // toggle aria-pressed
    langBtns.forEach(b => b.setAttribute("aria-pressed", String(b.dataset.lang === lang)));
    // update static texts
    $("#hero-title").textContent = getText("hero_title");
    $("#hero-sub").textContent = getText("hero_sub");
    $("#faq-title").textContent = getText("faq_title");
    $("#footer-txt").textContent = getText("footer");
    // placeholder and button
    $("#chat-input").setAttribute("placeholder", getText("placeholder"));
    $("#send-btn").textContent = getText("send");
  }
  function getText(key){
    const t = {
      es: {
        welcome: "¡Hola! Soy tu asistente. Puedo responder FAQs (precio, soporte, SEO, contacto) y guiarte. ¿En qué te ayudo?",
        typing: "Escribiendo…",
        error: "Hubo un problema. Intenta de nuevo.",
        network: "Error de red. ¿Puedes probar otra vez?",
        hero_title: "Asistente del sitio",
        hero_sub: "Responde 24/7, capta clientes y guía al usuario. Bilingüe (ES/FR).",
        faq_title: "Sugerencias",
        footer: "Hecho con HTML, CSS, JS y PHP. Una sola carpeta.",
        placeholder: "Escribe tu mensaje…",
        send: "Enviar"
      },
      fr: {
        welcome: "Bonjour ! Je suis votre assistant. Je peux répondre aux FAQ (prix, support, SEO, contact) et vous guider. De quoi avez‑vous besoin ?",
        typing: "Saisie…",
        error: "Un problème est survenu. Réessayez.",
        network: "Erreur réseau. Pouvez‑vous réessayer ?",
        hero_title: "Assistant du site",
        hero_sub: "Répond 24/7, capte des prospects et guide l’utilisateur. Bilingue (ES/FR).",
        faq_title: "Suggestions",
        footer: "Réalisé en HTML, CSS, JS et PHP. Un seul dossier.",
        placeholder: "Écrivez votre message…",
        send: "Envoyer"
      }
    };
    return (t[lang] && t[lang][key]) ? t[lang][key] : key;
  }
})();