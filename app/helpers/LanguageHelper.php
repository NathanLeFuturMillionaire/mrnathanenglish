<?php

namespace App\Helpers;

class LanguageHelper
{
  /**
   * Retourne toutes les langues maternelles de la planète,
   * classées par région géographique.
   *
   * @return array<string, array<string, string>>
   */
  public static function getAllLanguages(): array
  {
    return [

      // =============================================
      // GABON — LANGUES ET ETHNIES (en premier)
      // =============================================
      'Gabon' => [
        'aduma'         => 'Aduma',
        'adjumba'       => 'Adjumba (Myènè)',
        'akanigui'      => 'Akanigui',
        'akele'         => 'Akèlè',
        'apindji'       => 'Apindji',
        'awandji'       => 'Awandji',
        'babongo'       => 'Babongo (Pygmé)',
        'baka_ga'       => 'Baka (Pygmé)',
        'barimba'       => 'Barimba (Pygmé)',
        'batsagui'      => 'Batsagui',
        'benga'         => 'Benga',
        'beti_fang'     => 'Beti (Fang)',
        'bulu_fang'     => 'Bulu (Fang)',
        'bungom'        => 'Bungom',
        'chiwa'         => 'Chiwa',
        'enenga'        => 'Enenga (Myènè)',
        'eviya'         => 'Eviya',
        'fang_ga'       => 'Fang',
        'galoa'         => 'Galoa (Myènè)',
        'gisir'         => 'Gisir',
        'kota_ga'       => 'Kota',
        'kwele'         => 'Kwele',
        'lumbu'         => 'Lumbu',
        'mahongwe'      => 'Mahongwe (Kota)',
        'makina'        => 'Makina',
        'masango'       => 'Masango',
        'mbahouin'      => 'Mbahouin',
        'mbede'         => 'Mbédé / Mbété',
        'meke_fang'     => 'Meke (Fang)',
        'metombolo'     => 'Métombolo',
        'mpongwe'       => 'Mpongwé (Myènè)',
        'mvai_fang'     => 'Mvaï (Fang)',
        'mwele'         => 'Mwélé',
        'myene_ga'      => 'Myènè',
        'ndambomo'      => 'Ndambomo (Kota)',
        'ndasa'         => 'Ndasa (Kota)',
        'ndumu'         => 'Ndumu',
        'nkomi'         => 'Nkomi (Myènè)',
        'ntumu_fang'    => 'Ntumu (Fang)',
        'nzaman_fang'   => 'Nzaman (Fang)',
        'nzebi_ga'      => 'Nzebi',
        'obamba'        => 'Obamba',
        'okak_fang'     => 'Okak (Fang)',
        'okande'        => 'Okandè',
        'orungu'        => 'Orungu (Myènè)',
        'puvi'          => 'Puvi',
        'punu_ga'       => 'Punu',
        'sake_kota'     => 'Saké (Kota)',
        'shamaye'       => 'Shamaye (Kota)',
        'siho'          => 'Siho (Kota)',
        'sekiani'       => 'Sékiani',
        'simba'         => 'Simba',
        'teke_ga'       => 'Téké',
        'tsogo'         => 'Tsogo',
        'varama'        => 'Varama',
        'vili_sindara'  => 'Vili (Sindara)',
        'vili_kikongo'  => 'Vili Kikongo (Mayumba)',
        'vungu'         => 'Vungu',
        'wumbu'         => 'Wumbu (Kota)',
      ],

      // =============================================
      // AFRIQUE DE L'OUEST
      // =============================================
      'Afrique de l\'Ouest' => [
        'fon'           => 'Fon (Bénin)',
        'yoruba_ben'    => 'Yoruba (Bénin)',
        'bariba'        => 'Bariba (Bénin)',
        'moore'         => 'Mooré (Burkina Faso)',
        'dyula'         => 'Dioula (Burkina Faso / Côte d\'Ivoire)',
        'fulfude'       => 'Fulfuldé (Burkina Faso / Niger / Nigeria)',
        'wolof'         => 'Wolof (Sénégal / Gambie)',
        'pulaar'        => 'Pulaar / Peul (Sénégal / Guinée / Mali)',
        'serer'         => 'Sérère (Sénégal)',
        'diola'         => 'Diola (Sénégal / Gambie)',
        'mandinka'      => 'Mandinka (Gambie / Sénégal)',
        'soninke'       => 'Soninké (Mali / Sénégal / Mauritanie)',
        'bambara'       => 'Bambara (Mali)',
        'dogon'         => 'Dogon (Mali)',
        'songhai'       => 'Songhay (Mali / Niger)',
        'tamasheq'      => 'Tamasheq (Mali / Niger)',
        'hausa'         => 'Haoussa (Nigeria / Niger)',
        'igbo'          => 'Igbo (Nigeria)',
        'yoruba'        => 'Yoruba (Nigeria)',
        'ijaw'          => 'Ijaw (Nigeria)',
        'tiv'           => 'Tiv (Nigeria)',
        'kanuri'        => 'Kanouri (Nigeria / Niger / Tchad)',
        'efik'          => 'Efik (Nigeria)',
        'edo'           => 'Edo / Bini (Nigeria)',
        'nupe'          => 'Nupé (Nigeria)',
        'zarma'         => 'Zarma (Niger)',
        'gourmantche'   => 'Gourmantché (Niger / Burkina Faso)',
        'akan'          => 'Akan / Twi (Ghana / Côte d\'Ivoire)',
        'ewe'           => 'Éwé (Ghana / Togo)',
        'ga'            => 'Ga-Adangbe (Ghana)',
        'dagbani'       => 'Dagbani (Ghana)',
        'fante'         => 'Fante (Ghana)',
        'nzema'         => 'Nzema (Ghana)',
        'kabiye'        => 'Kabiyé (Togo)',
        'tem'           => 'Tem / Kotokoli (Togo / Bénin)',
        'gen'           => 'Gẽn / Mina (Togo / Bénin)',
        'baule'         => 'Baoulé (Côte d\'Ivoire)',
        'bete'          => 'Bété (Côte d\'Ivoire)',
        'guere'         => 'Guéré (Côte d\'Ivoire)',
        'senufo'        => 'Sénoufo (Côte d\'Ivoire / Mali)',
        'kru'           => 'Krou (Côte d\'Ivoire / Libéria)',
        'mende'         => 'Mendé (Sierra Leone)',
        'temne'         => 'Temné (Sierra Leone)',
        'krio'          => 'Krio (Sierra Leone)',
        'limba'         => 'Limba (Sierra Leone)',
        'susu'          => 'Soussou (Guinée)',
        'malinke'       => 'Malinké (Guinée / Mali)',
        'kissi'         => 'Kissi (Guinée / Sierra Leone)',
        'kpelle'        => 'Kpellé (Libéria / Guinée)',
        'bassa'         => 'Bassa (Libéria)',
        'grebo'         => 'Grébo (Libéria)',
        'vai'           => 'Vaï (Libéria / Sierra Leone)',
        'crioulo_gw'    => 'Criolo (Guinée-Bissau)',
        'balanta'       => 'Balanta (Guinée-Bissau)',
        'fula'          => 'Fula (Guinée-Bissau / Sénégal)',
        'cape_verde'    => 'Capverdien (Cap-Vert)',
        'sao_tome'      => 'Forro (São Tomé-et-Príncipe)',
        'annobones'     => 'Fa d\'Ambô (Guinée équatoriale / São Tomé)',
      ],

      // =============================================
      // AFRIQUE CENTRALE
      // =============================================
      'Afrique Centrale' => [
        'fang'          => 'Fang (Cameroun / Guinée équatoriale)',
        'myene'         => 'Myéné (Congo / Gabon)',
        'nzebi'         => 'Nzébi (Congo / Gabon)',
        'teke'          => 'Téké (Congo / Gabon / RDC)',
        'punu'          => 'Punu / Bapunu (Congo / Gabon)',
        'lingala'       => 'Lingala (RDC / Congo)',
        'kikongo'       => 'Kikongo (RDC / Congo / Angola)',
        'tshiluba'      => 'Tshiluba (RDC)',
        'swahili_cd'    => 'Swahili (RDC / Tanzanie / Kenya)',
        'kinyarwanda'   => 'Kinyarwanda (RDC / Rwanda)',
        'mongo'         => 'Mongo (RDC)',
        'luba'          => 'Luba (RDC)',
        'rwanda'        => 'Kinyarwanda (Rwanda)',
        'kirundi'       => 'Kirundi (Burundi)',
        'sango'         => 'Sango (République centrafricaine)',
        'gbaya'         => 'Gbaya (RCA / Cameroun)',
        'zande'         => 'Zandé (RCA / RDC / Soudan du Sud)',
        'sara'          => 'Sara (Tchad / RCA)',
        'arabic_chad'   => 'Arabe tchadien (Tchad)',
        'maba'          => 'Maba (Tchad)',
        'ewondo'        => 'Ewondo / Beti (Cameroun)',
        'bamileke'      => 'Bamiléké (Cameroun)',
        'fulfulde_cm'   => 'Fulfulde (Cameroun / Nigeria)',
        'duala'         => 'Duala (Cameroun)',
        'bassa_cm'      => 'Bassa (Cameroun)',
        'arabic_cm'     => 'Arabe choa (Cameroun / Tchad / Nigeria)',
        'bubi'          => 'Bubi (Guinée équatoriale)',
        'ndowe'         => 'Ndowé (Guinée équatoriale)',
      ],

      // =============================================
      // AFRIQUE DE L'EST
      // =============================================
      'Afrique de l\'Est' => [
        'swahili'       => 'Swahili / Kiswahili (Tanzanie / Kenya / Ouganda)',
        'sukuma'        => 'Sukuma (Tanzanie)',
        'chaga'         => 'Chaga (Tanzanie)',
        'makonde'       => 'Makonde (Tanzanie / Mozambique)',
        'haya'          => 'Haya (Tanzanie)',
        'gikuyu'        => 'Gikuyu / Kikuyu (Kenya)',
        'luo'           => 'Luo (Kenya / Ouganda)',
        'kamba'         => 'Kamba (Kenya)',
        'luhya'         => 'Luhya (Kenya)',
        'kalenjin'      => 'Kalenjin (Kenya)',
        'meru'          => 'Meru (Kenya)',
        'somali'        => 'Somali (Somalie / Éthiopie / Kenya)',
        'amharic'       => 'Amharique (Éthiopie)',
        'oromo'         => 'Oromo (Éthiopie)',
        'tigrinya'      => 'Tigrigna (Éthiopie / Érythrée)',
        'sidamo'        => 'Sidamo (Éthiopie)',
        'afar'          => 'Afar (Éthiopie / Érythrée / Djibouti)',
        'harari'        => 'Harari (Éthiopie)',
        'somali_dj'     => 'Somali (Djibouti)',
        'ganda'         => 'Luganda (Ouganda)',
        'acholi'        => 'Acholi (Ouganda)',
        'langi'         => 'Langi (Ouganda)',
        'lugbara'       => 'Lugbara (Ouganda / RDC)',
        'runyankore'    => 'Runyankore (Ouganda)',
        'malagasy'      => 'Malgache (Madagascar)',
        'comorian'      => 'Comorien / Shikomori (Comores)',
        'seychellois'   => 'Créole seychellois (Seychelles)',
        'morisyen'      => 'Morisyen / Créole mauricien (Maurice)',
      ],

      // =============================================
      // AFRIQUE DU NORD
      // =============================================
      'Afrique du Nord' => [
        'arabic_ma'     => 'Arabe marocain / Darija (Maroc)',
        'tamazight'     => 'Tamazight / Berbère (Maroc / Algérie)',
        'tachelhit'     => 'Tachelhit (Maroc)',
        'tarifit'       => 'Tarifit / Riffain (Maroc)',
        'arabic_dz'     => 'Arabe algérien (Algérie)',
        'kabyle'        => 'Kabyle (Algérie)',
        'chaoui'        => 'Chaoui (Algérie)',
        'arabic_tn'     => 'Arabe tunisien (Tunisie)',
        'arabic_ly'     => 'Arabe libyen (Libye)',
        'arabic_eg'     => 'Arabe égyptien (Égypte)',
        'coptic'        => 'Copte (Égypte)',
        'arabic_sd'     => 'Arabe soudanais (Soudan)',
        'nubien'        => 'Nubien (Soudan / Égypte)',
        'beja'          => 'Beja (Soudan / Érythrée)',
        'hassaniya'     => 'Hassaniya (Mauritanie / Maroc / Mali)',
      ],

      // =============================================
      // AFRIQUE AUSTRALE
      // =============================================
      'Afrique Australe' => [
        'zulu'          => 'Zulu (Afrique du Sud)',
        'xhosa'         => 'Xhosa (Afrique du Sud)',
        'sotho'         => 'Sotho du Sud (Afrique du Sud / Lesotho)',
        'tswana'        => 'Tswana (Afrique du Sud / Botswana)',
        'pedi'          => 'Pedi / Sotho du Nord (Afrique du Sud)',
        'venda'         => 'Venda (Afrique du Sud)',
        'tsonga'        => 'Tsonga (Afrique du Sud / Mozambique)',
        'swati'         => 'Swati (Afrique du Sud / Eswatini)',
        'ndebele'       => 'Ndébélé (Afrique du Sud / Zimbabwe)',
        'afrikaans'     => 'Afrikaans (Afrique du Sud / Namibie)',
        'shona'         => 'Shona (Zimbabwe)',
        'ndebele_zw'    => 'Ndébélé du Nord (Zimbabwe)',
        'chichewa'      => 'Chichewa / Nyanja (Malawi / Zambie)',
        'tumbuka'       => 'Tumbuka (Malawi / Zambie)',
        'bemba'         => 'Bemba (Zambie)',
        'lozi'          => 'Lozi (Zambie / Namibie)',
        'tonga_zm'      => 'Tonga (Zambie / Zimbabwe)',
        'portuguese_mz' => 'Portugais (Mozambique)',
        'makua'         => 'Makua (Mozambique)',
        'sena'          => 'Sena (Mozambique)',
        'herero'        => 'Herero (Namibie / Botswana)',
        'nama'          => 'Nama / Khoekhoegowab (Namibie)',
        'oshiwambo'     => 'Oshiwambo (Namibie)',
        'setswana'      => 'Setswana (Botswana)',
        'sesotho_ls'    => 'Sesotho (Lesotho)',
        'siswati'       => 'Siswati (Eswatini)',
        'swazi'         => 'Swazi (Eswatini / Afrique du Sud)',
        'portuguese_ao' => 'Portugais (Angola)',
        'umbundu'       => 'Umbundu (Angola)',
        'kimbundu'      => 'Kimbundu (Angola)',
        'kikongo_ao'    => 'Kikongo (Angola)',
      ],

      // =============================================
      // EUROPE DE L'OUEST
      // =============================================
      'Europe de l\'Ouest' => [
        'french'        => 'Français (France / Belgique / Suisse)',
        'german'        => 'Allemand (Allemagne / Autriche / Suisse)',
        'english'       => 'Anglais (Royaume-Uni / Irlande)',
        'spanish'       => 'Espagnol (Espagne)',
        'portuguese'    => 'Portugais (Portugal)',
        'italian'       => 'Italien (Italie)',
        'dutch'         => 'Néerlandais (Pays-Bas / Belgique)',
        'catalan'       => 'Catalan (Espagne / Andorre)',
        'galician'      => 'Galicien (Espagne)',
        'basque'        => 'Basque (Espagne / France)',
        'occitan'       => 'Occitan (France)',
        'breton'        => 'Breton (France)',
        'alsatian'      => 'Alsacien (France)',
        'corsican'      => 'Corse (France)',
        'luxembourgish' => 'Luxembourgeois (Luxembourg)',
        'flemish'       => 'Flamand (Belgique)',
        'walloon'       => 'Wallon (Belgique)',
        'swiss_german'  => 'Alémanique / Suisse allemand (Suisse)',
        'romansh'       => 'Romanche (Suisse)',
        'frisian'       => 'Frison (Pays-Bas)',
        'low_saxon'     => 'Bas-saxon (Allemagne / Pays-Bas)',
        'sorbian'       => 'Sorabe (Allemagne)',
        'irish'         => 'Irlandais (Irlande)',
        'welsh'         => 'Gallois (Pays de Galles)',
        'scottish'      => 'Gaélique écossais (Écosse)',
        'manx'          => 'Mannois (Île de Man)',
        'maltese'       => 'Maltais (Malte)',
        'sardinian'     => 'Sarde (Italie)',
        'friulian'      => 'Frioulan (Italie)',
        'ladin'         => 'Ladin (Italie)',
        'neapolitan'    => 'Napolitain (Italie)',
        'sicilian'      => 'Sicilien (Italie)',
        'venetian'      => 'Vénitien (Italie)',
      ],

      // =============================================
      // EUROPE DE L'EST ET DU NORD
      // =============================================
      'Europe de l\'Est et du Nord' => [
        'russian'       => 'Russe (Russie / Biélorussie / Kazakhstan)',
        'polish'        => 'Polonais (Pologne)',
        'czech'         => 'Tchèque (République tchèque)',
        'slovak'        => 'Slovaque (Slovaquie)',
        'hungarian'     => 'Hongrois (Hongrie)',
        'romanian'      => 'Roumain (Roumanie / Moldavie)',
        'bulgarian'     => 'Bulgare (Bulgarie)',
        'serbian'       => 'Serbe (Serbie)',
        'croatian'      => 'Croate (Croatie)',
        'bosnian'       => 'Bosnien (Bosnie)',
        'slovenian'     => 'Slovène (Slovénie)',
        'macedonian'    => 'Macédonien (Macédoine du Nord)',
        'albanian'      => 'Albanais (Albanie / Kosovo)',
        'greek'         => 'Grec (Grèce / Chypre)',
        'ukrainian'     => 'Ukrainien (Ukraine)',
        'belarusian'    => 'Biélorusse (Biélorussie)',
        'lithuanian'    => 'Lituanien (Lituanie)',
        'latvian'       => 'Letton (Lettonie)',
        'estonian'      => 'Estonien (Estonie)',
        'finnish'       => 'Finnois (Finlande)',
        'swedish'       => 'Suédois (Suède / Finlande)',
        'norwegian'     => 'Norvégien (Norvège)',
        'danish'        => 'Danois (Danemark)',
        'icelandic'     => 'Islandais (Islande)',
        'faroese'       => 'Féroïen (Îles Féroé)',
        'sami'          => 'Same (Scandinavie)',
        'moldovan'      => 'Moldave (Moldavie)',
        'romani'        => 'Romani (Europe)',
        'yiddish'       => 'Yiddish (Europe / Israël)',
      ],

      // =============================================
      // MOYEN-ORIENT ET ASIE CENTRALE
      // =============================================
      'Moyen-Orient et Asie Centrale' => [
        'arabic'        => 'Arabe standard (Monde arabe)',
        'arabic_sa'     => 'Arabe saoudien (Arabie Saoudite)',
        'arabic_iq'     => 'Arabe irakien (Irak)',
        'arabic_sy'     => 'Arabe syrien (Syrie)',
        'arabic_lb'     => 'Arabe libanais (Liban)',
        'arabic_jo'     => 'Arabe jordanien (Jordanie)',
        'arabic_ps'     => 'Arabe palestinien (Palestine)',
        'arabic_ye'     => 'Arabe yéménite (Yémen)',
        'arabic_om'     => 'Arabe omanais (Oman)',
        'arabic_ae'     => 'Arabe émirati (Émirats arabes unis)',
        'arabic_kw'     => 'Arabe koweïtien (Koweït)',
        'arabic_bh'     => 'Arabe bahreïni (Bahreïn)',
        'arabic_qa'     => 'Arabe qatarien (Qatar)',
        'hebrew'        => 'Hébreu (Israël)',
        'persian'       => 'Persan / Farsi (Iran)',
        'dari'          => 'Dari (Afghanistan)',
        'pashto'        => 'Pachto (Afghanistan / Pakistan)',
        'kurdish'       => 'Kurde (Irak / Syrie / Turquie / Iran)',
        'turkish'       => 'Turc (Turquie)',
        'azerbaijani'   => 'Azerbaïdjanais (Azerbaïdjan)',
        'armenian'      => 'Arménien (Arménie)',
        'georgian'      => 'Géorgien (Géorgie)',
        'kazakh'        => 'Kazakh (Kazakhstan)',
        'uzbek'         => 'Ouzbek (Ouzbékistan)',
        'turkmen'       => 'Turkmène (Turkménistan)',
        'tajik'         => 'Tadjik (Tadjikistan)',
        'kyrgyz'        => 'Kirghiz (Kirghizistan)',
        'uyghur'        => 'Ouïghour (Chine / Asie centrale)',
        'assyrian'      => 'Assyrien / Araméen (Irak / Syrie)',
      ],

      // =============================================
      // ASIE DU SUD
      // =============================================
      'Asie du Sud' => [
        'hindi'         => 'Hindi (Inde)',
        'bengali'       => 'Bengali (Bangladesh / Inde)',
        'urdu'          => 'Ourdou (Pakistan / Inde)',
        'punjabi'       => 'Pendjabi (Pakistan / Inde)',
        'marathi'       => 'Marathi (Inde)',
        'telugu'        => 'Télougou (Inde)',
        'tamil'         => 'Tamoul (Inde / Sri Lanka)',
        'gujarati'      => 'Gujarati (Inde)',
        'kannada'       => 'Kannada (Inde)',
        'malayalam'     => 'Malayalam (Inde)',
        'odia'          => 'Odia (Inde)',
        'assamese'      => 'Assamais (Inde)',
        'maithili'      => 'Maïthili (Inde / Népal)',
        'sindhi'        => 'Sindhi (Pakistan / Inde)',
        'nepali'        => 'Népalais (Népal / Inde)',
        'sinhala'       => 'Cingalais (Sri Lanka)',
        'dhivehi'       => 'Maldivien / Dhivehi (Maldives)',
        'dzongkha'      => 'Dzongkha (Bhoutan)',
        'newari'        => 'Newari (Népal)',
        'tibetan'       => 'Tibétain (Chine / Inde)',
        'santali'       => 'Santali (Inde)',
        'dogri'         => 'Dogri (Inde)',
        'konkani'       => 'Konkani (Inde)',
        'manipuri'      => 'Manipuri (Inde)',
        'kashmiri'      => 'Cachemiri (Inde / Pakistan)',
        'balochi'       => 'Baloutchi (Pakistan / Iran)',
      ],

      // =============================================
      // ASIE DU SUD-EST
      // =============================================
      'Asie du Sud-Est' => [
        'mandarin'      => 'Mandarin / Chinois (Chine / Taïwan / Singapour)',
        'cantonese'     => 'Cantonais (Chine / Hong Kong)',
        'wu'            => 'Shanghaïen / Wu (Chine)',
        'min_nan'       => 'Min Nan / Hokkien (Chine / Taïwan / Asie du SE)',
        'hakka'         => 'Hakka (Chine / Asie du SE)',
        'japanese'      => 'Japonais (Japon)',
        'korean'        => 'Coréen (Corée du Sud / Corée du Nord)',
        'vietnamese'    => 'Vietnamien (Vietnam)',
        'thai'          => 'Thaï (Thaïlande)',
        'burmese'       => 'Birman (Myanmar)',
        'khmer'         => 'Khmer (Cambodge)',
        'lao'           => 'Laotien (Laos)',
        'malay'         => 'Malais (Malaisie / Indonésie / Brunei)',
        'indonesian'    => 'Indonésien (Indonésie)',
        'javanese'      => 'Javanais (Indonésie)',
        'sundanese'     => 'Soundanais (Indonésie)',
        'balinese'      => 'Balinais (Indonésie)',
        'batak'         => 'Batak (Indonésie)',
        'minangkabau'   => 'Minangkabau (Indonésie)',
        'tagalog'       => 'Tagalog / Filipino (Philippines)',
        'cebuano'       => 'Cebuano (Philippines)',
        'ilocano'       => 'Ilocano (Philippines)',
        'hiligaynon'    => 'Hiligaynon (Philippines)',
        'waray'         => 'Waray (Philippines)',
        'kapampangan'   => 'Kapampangan (Philippines)',
        'tetum'         => 'Tetum (Timor oriental)',
        'shan'          => 'Shan (Myanmar)',
        'karen'         => 'Karen (Myanmar / Thaïlande)',
        'hmong'         => 'Hmong (Laos / Vietnam / Chine)',
        'singapore_malay' => 'Malay singapourien (Singapour)',
      ],

      // =============================================
      // ASIE DE L'EST
      // =============================================
      'Asie de l\'Est' => [
        'mongolian'     => 'Mongol (Mongolie / Chine)',
        'tibetan_cn'    => 'Tibétain (Chine)',
        'zhuang'        => 'Zhuang (Chine)',
        'manchu'        => 'Mandchou (Chine)',
        'korean_nk'     => 'Coréen du Nord (Corée du Nord)',
        'ryukyuan'      => 'Ryūkyūan (Japon)',
        'ainu'          => 'Aïnou (Japon)',
      ],

      // =============================================
      // AMÉRIQUE DU NORD
      // =============================================
      'Amérique du Nord' => [
        'english_us'    => 'Anglais américain (États-Unis)',
        'spanish_mx'    => 'Espagnol mexicain (Mexique)',
        'french_ca'     => 'Français canadien (Canada)',
        'english_ca'    => 'Anglais canadien (Canada)',
        'nahuatl'       => 'Nahuatl (Mexique)',
        'maya'          => 'Maya (Mexique / Guatemala)',
        'zapotec'       => 'Zapotèque (Mexique)',
        'mixtec'        => 'Mixtèque (Mexique)',
        'otomi'         => 'Otomi (Mexique)',
        'cherokee'      => 'Cherokee (États-Unis)',
        'navajo'        => 'Navajo (États-Unis)',
        'lakota'        => 'Lakota (États-Unis)',
        'ojibwe'        => 'Ojibwé (États-Unis / Canada)',
        'cree'          => 'Cri (Canada)',
        'inuktitut'     => 'Inuktitut (Canada)',
        'michif'        => 'Michif (Canada)',
        'hawaiian'      => 'Hawaïen (États-Unis)',
        'chamorro'      => 'Chamorro (Guam / Îles Mariannes)',
      ],

      // =============================================
      // AMÉRIQUE CENTRALE ET CARAÏBES
      // =============================================
      'Amérique Centrale et Caraïbes' => [
        'spanish_ca'    => 'Espagnol (Amérique centrale)',
        'creole_ht'     => 'Créole haïtien (Haïti)',
        'spanish_cu'    => 'Espagnol cubain (Cuba)',
        'spanish_do'    => 'Espagnol dominicain (Rép. dominicaine)',
        'spanish_pr'    => 'Espagnol portoricain (Porto Rico)',
        'papiamento'    => 'Papiamento (Aruba / Curaçao / Bonaire)',
        'garifuna'      => 'Garifuna (Honduras / Guatemala / Belize)',
        'miskito'       => 'Miskito (Nicaragua / Honduras)',
        'kekchi'        => 'Q\'eqchi\' (Guatemala / Belize)',
        'creole_bze'    => 'Créole bélizien (Belize)',
        'dutch_sr'      => 'Néerlandais (Suriname)',
        'sranan'        => 'Sranan Tongo (Suriname)',
        'javanese_sr'   => 'Javanais surinamien (Suriname)',
        'kweyol'        => 'Kréyòl (Martinique / Guadeloupe / Sainte-Lucie)',
      ],

      // =============================================
      // AMÉRIQUE DU SUD
      // =============================================
      'Amérique du Sud' => [
        'portuguese_br' => 'Portugais brésilien (Brésil)',
        'spanish_ar'    => 'Espagnol argentin (Argentine)',
        'spanish_co'    => 'Espagnol colombien (Colombie)',
        'spanish_ve'    => 'Espagnol vénézuélien (Venezuela)',
        'spanish_pe'    => 'Espagnol péruvien (Pérou)',
        'spanish_cl'    => 'Espagnol chilien (Chili)',
        'spanish_ec'    => 'Espagnol équatorien (Équateur)',
        'spanish_bo'    => 'Espagnol bolivien (Bolivie)',
        'spanish_py'    => 'Espagnol paraguayen (Paraguay)',
        'spanish_uy'    => 'Espagnol uruguayen (Uruguay)',
        'quechua'       => 'Quechua (Pérou / Bolivie / Équateur)',
        'aymara'        => 'Aymara (Bolivie / Pérou / Chili)',
        'guarani'       => 'Guarani (Paraguay / Argentine)',
        'mapuche'       => 'Mapuche / Mapudungun (Chili / Argentine)',
        'wayuu'         => 'Wayuu (Colombie / Venezuela)',
        'tukano'        => 'Tukano (Colombie / Brésil)',
        'awajun'        => 'Awajún (Pérou)',
        'shuar'         => 'Shuar (Équateur / Pérou)',
        'french_gy'     => 'Français (Guyane française)',
        'creole_gy'     => 'Créole guyanais (Guyane française)',
      ],

      // =============================================
      // OCÉANIE ET PACIFIQUE
      // =============================================
      'Océanie et Pacifique' => [
        'english_au'    => 'Anglais australien (Australie)',
        'english_nz'    => 'Anglais néo-zélandais (Nouvelle-Zélande)',
        'maori'         => 'Maori (Nouvelle-Zélande)',
        'aboriginal'    => 'Langues aborigènes (Australie)',
        'fijian'        => 'Fidjien (Fidji)',
        'hindi_fj'      => 'Hindi fidjien (Fidji)',
        'samoan'        => 'Samoan (Samoa / Samoa américaines)',
        'tongan'        => 'Tongien (Tonga)',
        'tahitian'      => 'Tahitien (Polynésie française)',
        'hawaiian_oc'   => 'Hawaïen (Hawaï)',
        'marshallese'   => 'Marshallais (Îles Marshall)',
        'palauan'       => 'Palauan (Palaos)',
        'yapese'        => 'Yapais (Micronésie)',
        'chuukese'      => 'Chuukais (Micronésie)',
        'nauruan'       => 'Nauruan (Nauru)',
        'kiribati'      => 'Gilbertin (Kiribati)',
        'tuvaluan'      => 'Tuvaluan (Tuvalu)',
        'tok_pisin'     => 'Tok Pisin (Papouasie-Nouvelle-Guinée)',
        'hiri_motu'     => 'Hiri Motu (Papouasie-Nouvelle-Guinée)',
        'bislama'       => 'Bichlamar (Vanuatu)',
        'solomon'       => 'Pijin (Îles Salomon)',
        'rotuman'       => 'Rotuman (Fidji)',
        'niuean'        => 'Niuéen (Niue)',
        'tokelauan'     => 'Tokélauan (Tokelau)',
        'wallisian'     => 'Wallisien (Wallis-et-Futuna)',
        'futunan'       => 'Futunien (Wallis-et-Futuna)',
      ],

    ];
  }

  /**
   * Retourne une liste plate clé => valeur pour les selects HTML
   * @return array<string, string>
   */
  public static function getFlatList(): array
  {
    $flat = [];
    foreach (self::getAllLanguages() as $languages) {
      foreach ($languages as $key => $label) {
        $flat[$key] = $label;
      }
    }
    return $flat;
  }

  /**
   * Retourne le libellé d'une langue par sa clé
   */
  public static function getLabel(string $key): string
  {
    return self::getFlatList()[$key] ?? 'Non renseigné';
  }
}
