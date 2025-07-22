<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Tailwind CSS output -->
    <link href="/css/output.css" rel="stylesheet" />
  </head>
  <body class="bg-gradient-to-r from-[#3A1C71] via-[#D76D77] to-[#FFAF7B] min-h-screen flex items-center justify-center px-4">

    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg w-full max-w-sm sm:max-w-md">
      <!-- Logo -->
      <img src="/images/logo.png" alt="Logo" class="w-32 sm:w-40 mx-auto mb-6 sm:mb-10" />

      <!-- Judul -->
      <h1 class="font-poppins text-xl sm:text-2xl font-semibold text-center">Welcome</h1>
      <p class="font-poppins text-gray-500 text-sm sm:text-base text-center mb-6 sm:mb-10">
        Enter your email and password to login
      </p>

      <!-- Form Login -->
      <form action="<?= base_url('login/proses') ?>" method="post" class="space-y-4">
        <!-- Notifikasi error -->
        <?php if (session()->getFlashdata('error')) : ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
              <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
          </div>
        <?php endif; ?>

        <!-- Email -->
        <div>
          <label for="email" class="font-poppins block text-sm font-medium text-black">Email</label>
          <input
            type="email"
            name="email"
            id="email"
            required
            class="font-poppins mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            placeholder="abcd@gmail.com"
            value="<?= old('email') ?>"
          />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="font-poppins block text-sm font-medium text-black">Password</label>
          <input
            type="password"
            name="password"
            id="password"
            required
            class="font-poppins mt-1 mb-8 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            placeholder="Enter your password"
          />
        </div>

        <!-- Tombol Login -->
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-150">
          Login
        </button>
      </form>

      <!-- Lupa password -->
      <div class="mt-4 text-right">
        <a href="<?= site_url('forget-password') ?>" class="font-poppins text-sm text-blue-600 hover:underline">
          Forgot your password?
        </a>
      </div>
    </div>
  </body>
</html>
