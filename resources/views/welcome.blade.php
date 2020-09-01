<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
      @if (session('message'))
        {{ session('message') }}
      @endif
      @if ($user->exists())
        id : {{ $user->id() }} <br><br>
        <form class="" action="/api/v1/services/payment" method="post">
          <input type="submit" name="" value="Bayar">
          <a href="/logout">Logout</a>
        </form>
      @else
        <form class="" action="/api/v1/auth/login" method="post">
          <input type="text" name="email" value="tokohp90@gmail.com"> <br/>
          <input type="text" name="password" value="katasandibaru"> <br/>
          <input type="submit" name="" value="Login">
        </form>
      @endif
      {{-- <script src="https://unpkg.com/axios/dist/axios.min.js"></script> --}}
      @if (session('token'))
          <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ session('token') }}"></script>
          <script type="text/javascript">
              snap.pay("{{ session('token') }}", {
                  onSuccess: function (result) {
                      console.log("success");
                      console.log(result);
                  },
                  // Optional
                  onPending: function (result) {
                      console.log("pending");
                      console.log(result);
                  },
                  // Optional
                  onError: function (result) {
                      console.log("error");
                      console.log(result);
                  }
              });
          </script>
      @endif
    </body>
</html>
