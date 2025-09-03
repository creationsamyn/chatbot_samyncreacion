<?php
// Simple JSON API for the chatbot (rules engine; optional IA stub).
// Keep this file in the same folder as index.html (same origin).

header("Content-Type: application/json; charset=utf-8");
// If you plan to call from a different origin, uncomment the next line:
// header("Access-Control-Allow-Origin: *");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$message = isset($data["message"]) ? sanitize($data["message"]) : "";
$lang = isset($data["lang"]) ? strtolower($data["lang"]) : "es";
if ($lang !== "fr") { $lang = "es"; }

if (!$message) {
  echo json_encode([ "ok" => true, "reply" => ($lang === "fr" ?
    "Dites-moi ce dont vous avez besoin. J’écoute.":
    "Cuéntame qué necesitas. Estoy aquí para ayudarte."
  )]);
  exit;
}

// OPTIONAL: Use an AI provider if desired (server-side).
// Define your key in an environment variable or below and uncomment call_ai().
// $ai_key = getenv("MYBOT_OPENAI_API_KEY");
// if ($ai_key) {
//   $reply = call_ai($message, $lang, $ai_key);
//   echo json_encode(["ok"=>true,"reply"=>$reply]);
//   exit;
// }

$reply = rules_engine($message, $lang);
echo json_encode(["ok"=>true,"reply"=>$reply]);
exit;

// ---- Helpers --------------------------------------------------------------

function sanitize($s){
  $s = trim($s);
  $s = strip_tags($s);
  // Remove excessive whitespace
  $s = preg_replace("/\s+/", " ", $s);
  return $s;
}

function rules_engine($text, $lang){
  $t = mb_strtolower($text, "UTF-8");

  // Keyword sets (you can expand with regex or partial scores)
  $keywords_es = [
    "precio"   => "Nuestros planes empiezan en 350€ y se ajustan al alcance. ¿Qué necesitas construir?",
    "soporte"  => "Ofrezco mantenimiento mensual con respuesta rápida. ¿Prefieres tickets o WhatsApp Business?",
    "seo"      => "Hago SEO técnico (Core Web Vitals, schema, sitemap) y de contenidos. ¿Tienes una URL para revisar?",
    "contacto" => "Puedes escribirme al correo: alsamyn@gmail.com o usar el formulario de contacto del sitio."
  ];
  $keywords_fr = [
    "prix"     => "Nos forfaits démarrent à 350€ et s’adaptent au périmètre. Que souhaitez‑vous réaliser ?",
    "support"  => "Maintenance mensuelle avec réactivité. Préférez-vous tickets ou WhatsApp Business ?",
    "seo"      => "Je fais du SEO technique (Core Web Vitals, schéma, sitemap) et éditorial. Avez-vous une URL à auditer ?",
    "contact"  => "Vous pouvez m’écrire à : alsamyn@gmail.com ou via le formulaire de contact du site."
  ];

  $map = ($lang === "fr") ? $keywords_fr : $keywords_es;

  foreach($map as $k => $ans){
    if (mb_strpos($t, $k, 0, "UTF-8") !== false){
      return $ans;
    }
  }

  // Fallbacks if user typed the other language
  foreach(( $lang === "fr" ? $keywords_es : $keywords_fr) as $k => $ans){
    if (mb_strpos($t, $k, 0, "UTF-8") !== false){
      return $ans;
    }
  }

  // Default answer
  if ($lang === "fr"){
    return "Merci pour votre message. Je peux vous aider en développement WordPress, SEO et automatisations. Pouvez‑vous m’en dire plus ?";
  }
  return "Gracias por tu mensaje. Puedo ayudarte con desarrollo WordPress, SEO y automatizaciones. ¿Puedes contarme un poco más?";
}

// OPTIONAL AI stub (OpenAI Chat Completions format)
// function call_ai($prompt, $lang, $api_key){
//   $sys = ($lang === "fr")
//     ? "Tu es un assistant pour un site WordPress. Réponds de manière brève et utile en français."
//     : "Eres un asistente para un sitio WordPress. Responde breve y útil en español.";
//   $payload = [
//     "model" => "gpt-4o-mini",
//     "messages" => [
//       ["role"=>"system","content"=>$sys],
//       ["role"=>"user","content"=>$prompt]
//     ],
//     "temperature" => 0.3
//   ];
//   $ch = curl_init("https://api.openai.com/v1/chat/completions");
//   curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     "Authorization: Bearer ".$api_key,
//     "Content-Type: application/json"
//   ]);
//   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//   curl_setopt($ch, CURLOPT_POST, true);
//   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
//   curl_setopt($ch, CURLOPT_TIMEOUT, 20);
//   $response = curl_exec($ch);
//   if ($response === false) return ($lang==="fr"?"Erreur de connexion à l’IA.":"Error de conexión con la IA.");
//   $data = json_decode($response, true);
//   $txt = $data["choices"][0]["message"]["content"] ?? null;
//   return $txt ?: ($lang==="fr"?"Réponse IA invalide.":"Respuesta de IA inválida.");
// }
