Butun proje docker containerlari icindedir.
Projeyi ayaga kaldirmak icin kodu cekip, <b>docker-compose up</b> yapmak yeterli.
Containerlar:

<b>subscription-management:</b>
purchase,register,check_subs endpointlerinin oldugu api

<b>subscription-management-mock-api:</b>
gonderilen receipt'e gore sonuc donen mock(Google/iOS) api.

<b>subscription-management-scheduler:</b>
artisan schedule:work'u calistiran ve dakikalik calisan kodu barindiran scheduler.

<b>subscription-management-unit-tests:</b>
containerlar ayaga kalktiktan sonra gerekli butun unit testleri calistirip kendini olduren container.

<b>mysql_db:</b>
butun containerlarin kullandigi db

<b>webhook-receiver:</b>
renewed, started, cancelled callbacklerinin gonderildigi webhook endpointi(calistigini anlamak adina test icin).

Butun containerlar(sql haric) ayni image'i, farkli portlardan farkli endpointleri kullaniyor.
