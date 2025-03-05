// src/api/fetchPlugins.js
export default async function fetchPlugins() {
    try {
        const response = await fetch('/api/plugins/frontend');
        if (!response.ok) {
            console.error('Ошибка: сервер вернул статус', response.status);
            return [];
        }

        const data = await response.json();
        console.log("Ответ от API:", data);

        if (!data.plugins || !Array.isArray(data.plugins)) {
            throw new Error("API вернул некорректный формат (нет массива plugins).");
        }

        // Динамически грузим каждый plugin.js
        const pluginModules = await Promise.all(
            data.plugins.map(async (plugin) => {
                // Проверка HEAD
                try {
                    const headResp = await fetch(plugin.url, { method: 'HEAD' });
                    if (!headResp.ok) {
                        console.error(`Плагин ${plugin.slug} недоступен, статус: ${headResp.status}`);
                        return null;
                    }
                    const contentType = headResp.headers.get('Content-Type') || '';
                    if (!contentType.includes('javascript')) {
                        console.error(`Плагин ${plugin.slug} имеет тип: "${contentType}" — не JS, пропускаем`);
                        return null;
                    }
                } catch (headError) {
                    console.error(`Ошибка HEAD для плагина ${plugin.slug}:`, headError);
                    return null;
                }

                // Импортируем сам JS-файл
                try {
                    const module = await import(/* @vite-ignore */ plugin.url);
                    if (typeof module.default === 'undefined') {
                        console.error(`У плагина ${plugin.slug} отсутствует "export default", пропускаем`);
                        return null;
                    }
                    return module.default;
                } catch (importError) {
                    console.error(`Ошибка при импорте плагина ${plugin.slug}:`, importError);
                    return null;
                }
            })
        );

        return pluginModules.filter(Boolean);
    } catch (error) {
        console.error("Ошибка загрузки списка плагинов:", error);
        return [];
    }
}
