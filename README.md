Butun proje docker containerlari icindedir.
Projeyi ayaga kaldirmak icin kodu cekip, docker-compose up yapmak yeterli.
Containerlar:

subscription-management:
purchase,register,check_subs endpointlerinin oldugu api

subscription-management-mock-api:
gonderilen receipt'e gore sonuc donen mock(Google/iOS) api.

subscription-management-scheduler:
artisan schedule:work'u calistiran ve dakikalik calisan kodu barindiran scheduler.

subscription-management-unit-tests:
containerlar ayaga kalktiktan sonra gerekli butun unit testleri calistirip kendini olduren container.

mysql_db:
butun containerlarin kullandigi db

webhook-receiver:
renewed, started, cancelled callbacklerinin gonderildigi webhook endpointi(calistigini anlamak adina test icin).

Butun containerlar image'i, farkli portlardan farkli endpointleri kullaniyor.
