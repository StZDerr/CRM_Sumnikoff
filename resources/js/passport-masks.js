import IMask from "imask";

document.addEventListener("DOMContentLoaded", () => {
    const series = document.getElementById("passport_series");
    if (series) {
        IMask(series, {
            mask: "0000",
        });
    }

    const number = document.getElementById("passport_number");
    if (number) {
        IMask(number, {
            mask: "000000",
        });
    }

    const departmentCode = document.getElementById("passport_department_code");
    if (departmentCode) {
        IMask(departmentCode, {
            mask: "000-000",
        });
    }
});
