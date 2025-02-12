
type RuleType = 'string' | 'number' | 'boolean' | 'method' | 'regexp' | 'integer' | 'float' | 'array' | 'object' | 'enum' | 'date' | 'url' | 'hex' | 'email' | 'pattern';
interface ValidateOption {
    suppressWarning?: boolean;
    suppressValidatorError?: boolean;
    first?: boolean;
    firstFields?: boolean | string[];
    messages?: Partial<ValidateMessages>;
    keys?: string[];
    error?: (rule: InternalRuleItem, message: string) => ValidateError;
}
type SyncErrorType = Error | string;
type SyncValidateResult = boolean | SyncErrorType | SyncErrorType[];
interface RuleItem {
    type?: RuleType;
    required?: boolean;
    pattern?: RegExp | string;
    min?: number;
    max?: number;
    len?: number;
    enum?: Array<string | number | boolean | null | undefined>;
    whitespace?: boolean;
    fields?: Record<string, Rule>;
    options?: ValidateOption;
    defaultField?: Rule;
    transform?: (value: Value) => Value;
    message?: string | ((a?: string) => string);
    asyncValidator?: (rule: InternalRuleItem, value: Value, callback: (error?: string | Error) => void, source: Values, options: ValidateOption) => void | Promise<void>;
    validator?: (rule: InternalRuleItem, value: Value, callback: (error?: string | Error) => void, source: Values, options: ValidateOption) => SyncValidateResult | void;
}
type Rule = RuleItem | RuleItem[];
type ExecuteValidator = (rule: InternalRuleItem, value: Value, callback: (error?: string[]) => void, source: Values, options: ValidateOption) => void;
type ValidateMessage<T extends any[] = unknown[]> = string | ((...args: T) => string);
type FullField = string | undefined;
type EnumString = string | undefined;
type Pattern = string | RegExp | undefined;
type Range = number | undefined;
type Type = string | undefined;
interface ValidateMessages {
    default?: ValidateMessage;
    required?: ValidateMessage<[FullField]>;
    enum?: ValidateMessage<[FullField, EnumString]>;
    whitespace?: ValidateMessage<[FullField]>;
    date?: {
        format?: ValidateMessage;
        parse?: ValidateMessage;
        invalid?: ValidateMessage;
    };
    types?: {
        string?: ValidateMessage<[FullField, Type]>;
        method?: ValidateMessage<[FullField, Type]>;
        array?: ValidateMessage<[FullField, Type]>;
        object?: ValidateMessage<[FullField, Type]>;
        number?: ValidateMessage<[FullField, Type]>;
        date?: ValidateMessage<[FullField, Type]>;
        boolean?: ValidateMessage<[FullField, Type]>;
        integer?: ValidateMessage<[FullField, Type]>;
        float?: ValidateMessage<[FullField, Type]>;
        regexp?: ValidateMessage<[FullField, Type]>;
        email?: ValidateMessage<[FullField, Type]>;
        url?: ValidateMessage<[FullField, Type]>;
        hex?: ValidateMessage<[FullField, Type]>;
    };
    string?: {
        len?: ValidateMessage<[FullField, Range]>;
        min?: ValidateMessage<[FullField, Range]>;
        max?: ValidateMessage<[FullField, Range]>;
        range?: ValidateMessage<[FullField, Range, Range]>;
    };
    number?: {
        len?: ValidateMessage<[FullField, Range]>;
        min?: ValidateMessage<[FullField, Range]>;
        max?: ValidateMessage<[FullField, Range]>;
        range?: ValidateMessage<[FullField, Range, Range]>;
    };
    array?: {
        len?: ValidateMessage<[FullField, Range]>;
        min?: ValidateMessage<[FullField, Range]>;
        max?: ValidateMessage<[FullField, Range]>;
        range?: ValidateMessage<[FullField, Range, Range]>;
    };
    pattern?: {
        mismatch?: ValidateMessage<[FullField, Value, Pattern]>;
    };
}
type Value = any;
type Values = Record<string, Value>;
interface ValidateError {
    message?: string;
    fieldValue?: Value;
    field?: string;
}
interface InternalRuleItem extends Omit<RuleItem, 'validator'> {
    field?: string;
    fullField?: string;
    fullFields?: string[];
    validator?: RuleItem['validator'] | ExecuteValidator;
}
type showToastOptions = { mask?: boolean, zIndex?: number, duration?: number };

/**
 * @description 验证
 * @param { Record<string, unknown> } formData 数据
 * @param { Record<string, Rule> } rules 规则
 * @param { showToastOptions } options uni.showToast 配置项
 */
declare function validate(formData: Record<string, unknown>, rules: Record<string, Rule>, options?: showToastOptions): void;


export default validate;
export type { RuleItem, showToastOptions };