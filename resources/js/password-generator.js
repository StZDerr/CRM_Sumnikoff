document.addEventListener("DOMContentLoaded", () => {
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("password_confirmation");
    const generatedInput = document.getElementById("generatedPassword");
    const generateBtn = document.getElementById("generatePassword");
    const copyBtn = document.getElementById("copyPassword");

    function generatePassword(length = 12) {
        const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        const lower = "abcdefghijklmnopqrstuvwxyz";
        const numbers = "0123456789";
        const symbols = "!@#$%^&*()_+{}[]<>?";
        let password = "";

        // Гарантируем хотя бы 1 символ каждого типа
        password += upper.charAt(Math.floor(Math.random() * upper.length));
        password += lower.charAt(Math.floor(Math.random() * lower.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += symbols.charAt(Math.floor(Math.random() * symbols.length));

        const allChars = upper + lower + numbers + symbols;
        for (let i = password.length; i < length; i++) {
            password += allChars.charAt(
                Math.floor(Math.random() * allChars.length),
            );
        }

        // Перемешиваем символы
        return password
            .split("")
            .sort(() => Math.random() - 0.5)
            .join("");
    }

    // Генерация пароля
    if (generateBtn) {
        generateBtn.addEventListener("click", () => {
            const password = generatePassword();
            passwordInput.value = password;
            confirmInput.value = password;
            generatedInput.value = password;
        });
    }

    // Копирование в буфер
    if (copyBtn) {
        copyBtn.addEventListener("click", () => {
            generatedInput.select();
            generatedInput.setSelectionRange(0, 99999); // для мобильных
            document.execCommand("copy");
            alert("Пароль скопирован в буфер");
        });
    }
});
