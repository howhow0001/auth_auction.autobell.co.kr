### Usage:
```bash
$ # with php (Recomended)
$ php get_token.php "<username>" "<password>"
$ # with curl ()
$ curl https://auction.autobell.co.kr/api/auction/requestAuctionLogin.do \
  -A "Windows 10" \
  -G \
  --data "userId=<ARIA256>" \
  --data "userPassword=<BASE64->ARIA256>" \
  --data "loginType=TOTAL&flagOtpAuth=&smsAuthNumber=&authToken=&tokenType="
```

### Info:
```
ARIA key: myXyzfamily-aria-password
```

### Request:
```
https://auction.autobell.co.kr/api/auction/requestAuctionLogin.do
   auction-autobell-co-kr ?loginType=TOTAL

    &userId=<ARIA256>
    &userPassword=<BASE64->ARIA256>

    &flagOtpAuth=
    &smsAuthNumber=
    &authToken=
    &tokenType=
```
