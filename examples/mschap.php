<?php
/*
Copyright (c) 2002-2003, Michael Bretterklieber <michael@bretterklieber.com>
All rights reserved.
 
Redistribution and use in source and binary forms, with or without 
modification, are permitted provided that the following conditions 
are met:
 
1. Redistributions of source code must retain the above copyright 
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright 
   notice, this list of conditions and the following disclaimer in the 
   documentation and/or other materials provided with the distribution.
3. Neither the name Michael Bretterklieber nor the names of its contributors 
   may be used to endorse or promote products derived from this software without 
   specific prior written permission.
 
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 
This code cannot simply be copied and put under the GNU Public License or 
any other GPL-like (LGPL, GPL2) License.

    $Id$
*/

include_once('des.php');

function NtPasswordHash($plain) 
{
    return mhash (MHASH_MD4, str2unicode($plain));
}

function str2unicode($str) 
{

    for ($i=0;$i<strlen($str);$i++) {
        $a = ord($str{$i}) << 8;
        $uni .= sprintf("%X",$a);
    }
    return pack('H*', $uni);
}

function GenerateChallenge() 
{
    mt_srand((double)microtime()*1000000);
    return pack('H16', sprintf("%X%X%X", mt_rand(), mt_rand(), mt_rand()));
}

function ChallengeResponse($challenge, $nthash) 
{
    while (strlen($nthash) < 21)
        $nthash .= "\0";

    $resp1 = des_encrypt_ecb(substr($nthash, 0, 7), $challenge);
    $resp2 = des_encrypt_ecb(substr($nthash, 7, 7), $challenge);
    $resp3 = des_encrypt_ecb(substr($nthash, 14, 7), $challenge);

    return $resp1 . $resp2 . $resp3;
}

// MS-CHAPv2

function GeneratePeerChallenge() 
{
    mt_srand((double)microtime()*1000000);

    return pack('H32', sprintf("%X%X%X%X%X", mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand()));
}

function NtPasswordHashHash($hash) 
{
    return mhash (MHASH_MD4, $hash);
}

function ChallengeHash($challenge, $peerChallenge, $username) 
{
    return substr(mhash (MHASH_SHA1, $peerChallenge . $challenge . $username), 0, 8);
}

function GenerateNTResponse($challenge, $peerChallenge, $username, $password) 
{
    $challengeHash = ChallengeHash($challenge, $peerChallenge, $username);
    $pwhash = NtPasswordHash($password);
    return ChallengeResponse($challengeHash, $pwhash);
}

?>
