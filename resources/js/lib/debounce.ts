export function debounce<T extends (...args: any[]) => any>(fn: T, delay = 400) {
    let timer: ReturnType<typeof setTimeout>;

    return function (this: any, ...args: Parameters<T>) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}