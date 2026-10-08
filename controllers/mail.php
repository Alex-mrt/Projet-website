<?php
// =====================================================================
//  ENVOI D'E-MAILS (SMTP natif, sans bibliothèque externe)
//  Les réglages sont dans config/config.php
// =====================================================================

function envoyerMail(string $destinataire, string $sujet, string $html, string $texte): bool {
    $frontiere = 'bda_' . bin2hex(random_bytes(8));
    $sujetEncode = '=?UTF-8?B?' . base64_encode($sujet) . '?=';
    $deEncode = '=?UTF-8?B?' . base64_encode(MAIL_NOM) . '?= <' . MAIL_EXPEDITEUR . '>';

    $corps = "--$frontiere\r\n"
           . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
           . chunk_split(base64_encode($texte)) . "\r\n"
           . "--$frontiere\r\n"
           . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
           . chunk_split(base64_encode($html)) . "\r\n"
           . "--$frontiere--\r\n";

    $entetes = [
        'From: ' . $deEncode,
        'Reply-To: ' . MAIL_EXPEDITEUR,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $frontiere . '"',
    ];

    try {
        if (MAIL_METHODE === 'mail') {
            return mail($destinataire, $sujetEncode, $corps, implode("\r\n", $entetes), '-f' . MAIL_EXPEDITEUR);
        }
        $message = implode("\r\n", array_merge([
            'Date: ' . date('r'),
            'To: <' . $destinataire . '>',
            'Subject: ' . $sujetEncode,
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . DOMAINE_AUTORISE . '>',
        ], $entetes)) . "\r\n\r\n" . $corps;
        return envoyerSmtp($destinataire, $message);
    } catch (Throwable $e) {
        error_log('[BDA] Envoi e-mail impossible : ' . $e->getMessage());
        return false;
    }
}

function envoyerSmtp(string $destinataire, string $message): bool {
    $port = (int) SMTP_PORT;
    $hote = ($port === 465 ? 'ssl://' : 'tcp://') . SMTP_HOTE . ':' . $port;

    $socket = @stream_socket_client($hote, $errno, $errstr, 10);
    if (!$socket) {
        throw new RuntimeException("Connexion SMTP impossible : $errstr");
    }
    stream_set_timeout($socket, 10);

    $lire = function () use ($socket): string {
        $reponse = '';
        while (($ligne = fgets($socket, 515)) !== false) {
            $reponse .= $ligne;
            if (isset($ligne[3]) && $ligne[3] === ' ') {
                break;
            }
        }
        return $reponse;
    };
    $commande = function (string $cmd, array $codesOk) use ($socket, $lire): string {
        if ($cmd !== '') {
            fwrite($socket, $cmd . "\r\n");
        }
        $reponse = $lire();
        if (!in_array((int) substr($reponse, 0, 3), $codesOk, true)) {
            throw new RuntimeException('SMTP a refusé la commande : ' . trim($reponse));
        }
        return $reponse;
    };

    $nomMachine = parse_url(APP_URL, PHP_URL_HOST) ?: 'localhost';

    $commande('', [220]);
    $commande('EHLO ' . $nomMachine, [250]);
    if ($port !== 465) {
        $commande('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
            throw new RuntimeException('Impossible d\'activer TLS.');
        }
        $commande('EHLO ' . $nomMachine, [250]);
    }
    $commande('AUTH LOGIN', [334]);
    $commande(base64_encode(SMTP_USER), [334]);
    $commande(base64_encode(SMTP_PASS), [235]);
    $commande('MAIL FROM:<' . MAIL_EXPEDITEUR . '>', [250]);
    $commande('RCPT TO:<' . $destinataire . '>', [250, 251]);
    $commande('DATA', [354]);

    $message = preg_replace('/\r?\n/', "\r\n", $message);
    $message = preg_replace('/^\./m', '..', $message);
    $commande($message . "\r\n.", [250]);
    $commande('QUIT', [221]);
    fclose($socket);
    return true;
}
