from datasets import load_dataset
from transformers import AutoModelForCausalLM, AutoTokenizer, TrainingArguments, Trainer
from peft import get_peft_model, LoraConfig, TaskType

model_name = "microsoft/phi-2"
dataset = load_dataset("json", data_files="dataset/train.jsonl", split="train")

tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForCausalLM.from_pretrained(model_name, device_map="auto", torch_dtype="auto")

peft_config = LoraConfig(task_type=TaskType.CAUSAL_LM, r=8, lora_alpha=16, lora_dropout=0.1)
model = get_peft_model(model, peft_config)

def tokenize(sample):
    prompt = f"<s>[INST] {sample['instruction']} [/INST] {sample['output']} </s>"
    return tokenizer(prompt, truncation=True, padding="max_length", max_length=512)

tokenized_dataset = dataset.map(tokenize)

training_args = TrainingArguments(
    output_dir="./viettransit_model",
    num_train_epochs=3,
    per_device_train_batch_size=4,
    logging_dir="./logs",
    save_total_limit=2,
    save_strategy="epoch"
)

trainer = Trainer(
    model=model,
    args=training_args,
    train_dataset=tokenized_dataset,
)
trainer.train()
