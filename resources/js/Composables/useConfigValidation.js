export function useConfigValidation() {
    const validateConfig = (config) => {
        const errors = {};
        
        // Required fields
        const requiredFields = {
            ai_name: 'Tên cấu hình',
            type: 'Loại cấu hình',
            context: 'Nội dung cấu hình',
            language: 'Ngôn ngữ',
            model_type: 'Model AI'
        };

        Object.entries(requiredFields).forEach(([field, label]) => {
            if (!config[field]?.toString().trim()) {
                errors[field] = `${label} là bắt buộc`;
            }
        });

        // Numeric validations
        const numericFields = {
            temperature: { min: 0, max: 1 },
            top_p: { min: 0, max: 1 },
            top_k: { min: 1, max: 100 },
            max_tokens: { min: 1, max: 8192 }
        };

        Object.entries(numericFields).forEach(([field, range]) => {
            const value = parseFloat(config[field]);
            if (isNaN(value) || value < range.min || value > range.max) {
                errors[field] = `${field} phải từ ${range.min} đến ${range.max}`;
            }
        });

        // JSON field validations
        const jsonFields = ['function_declarations', 'safety_settings', 'tool_config'];
        jsonFields.forEach(field => {
            if (config[field]) {
                try {
                    if (typeof config[field] === 'string') {
                        JSON.parse(config[field]);
                    }
                } catch (e) {
                    errors[field] = `${field} không đúng định dạng JSON`;
                }
            }
        });

        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    };

    return {
        validateConfig
    };
} 